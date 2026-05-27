<?php

namespace app\live\controllers;

use app\live\models\LiveSession;
use app\live\models\LiveSessionQuestion;
use app\live\models\LiveSessionRankSnapshot;
use app\live\models\LiveSessionSubmission;
use app\live\services\LiveLeaderboardService;
use app\live\services\LiveSessionManager;
use app\models\Submission;
use app\models\Quiz;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class TeacherController extends Controller
{
    private LiveSessionManager $sessionManager;
    private LiveLeaderboardService $leaderboardService;

    public function init()
    {
        parent::init();
        $this->sessionManager = new LiveSessionManager();
        $this->leaderboardService = new LiveLeaderboardService();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'open-next' => ['POST'],
                    'close-question' => ['POST'],
                    'finish' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $selectedQuizId = (int)Yii::$app->request->get('quiz_id', 0);
        $selectedQuiz = null;

        $quizzes = Quiz::find()
            ->where(['archived' => 0])
            ->orderBy(['quiz_group' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        foreach ($quizzes as $quiz) {
            if ((int)$quiz->id === $selectedQuizId) {
                $selectedQuiz = $quiz;
                break;
            }
        }

        if ($selectedQuiz === null) {
            $selectedQuizId = 0;
        }

        $sessions = new ActiveDataProvider([
            'query' => LiveSession::find()->with('quiz')->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'quizzes' => $quizzes,
            'sessions' => $sessions,
            'selectedQuizId' => $selectedQuizId,
            'selectedQuiz' => $selectedQuiz,
        ]);
    }

    public function actionCreate()
    {
        $quizId = (int)Yii::$app->request->post('quiz_id');
        try {
            $session = $this->sessionManager->createSession($quizId, Yii::$app->user->id ? (int)Yii::$app->user->id : null);
        } catch (\Throwable $throwable) {
            Yii::$app->session->setFlash('error', $throwable->getMessage());
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('success', 'Live session created. Students can now join the lobby.');
        return $this->redirect(['view', 'id' => $session->id]);
    }

    public function actionView(int $id)
    {
        $session = $this->findSession($id);
        $currentQuestion = $this->sessionManager->getCurrentSessionQuestion($session);
        $participants = LiveSessionSubmission::find()
            ->where(['live_session_id' => $session->id])
            ->joinWith('submission')
            ->orderBy(['submission.first_name' => SORT_ASC, 'submission.last_name' => SORT_ASC])
            ->all();

        $leaderboard = $this->leaderboardService->buildLeaderboard($session);
        $presentation = $this->leaderboardService->buildPresentationData($session, $currentQuestion, $leaderboard);
        $answerCount = 0;

        if ($currentQuestion !== null) {
            $answerCount = (int)(new \yii\db\Query())
                ->from('log')
                ->where([
                    'live_session_id' => $session->id,
                    'live_session_question_id' => $currentQuestion->id,
                ])
                ->count('*');
        }

        return $this->render('view', [
            'session' => $session,
            'currentQuestion' => $currentQuestion,
            'participants' => $participants,
            'leaderboard' => $leaderboard,
            'presentation' => $presentation,
            'answerCount' => $answerCount,
        ]);
    }

    public function actionOpenNext(int $id)
    {
        $session = $this->findSession($id);
        try {
            $this->sessionManager->openNextQuestion($session);
        } catch (\Throwable $throwable) {
            Yii::$app->session->setFlash('error', $throwable->getMessage());
        }
        return $this->redirect(['view', 'id' => $session->id]);
    }

    public function actionCloseQuestion(int $id)
    {
        $session = $this->findSession($id);
        try {
            $this->sessionManager->closeCurrentQuestion($session, $this->leaderboardService);
        } catch (\Throwable $throwable) {
            Yii::$app->session->setFlash('error', $throwable->getMessage());
        }
        return $this->redirect(['view', 'id' => $session->id]);
    }

    public function actionFinish(int $id)
    {
        $session = $this->findSession($id);
        try {
            $this->sessionManager->finishSession($session);
        } catch (\Throwable $throwable) {
            Yii::$app->session->setFlash('error', $throwable->getMessage());
        }
        return $this->redirect(['view', 'id' => $session->id]);
    }

    public function actionDelete(int $id)
    {
        $session = $this->findSession($id);

        if ($session->status !== LiveSession::STATUS_FINISHED) {
            Yii::$app->session->setFlash('error', 'Only finished live sessions can be deleted.');
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $submissionIds = LiveSessionSubmission::find()
                ->select('submission_id')
                ->where(['live_session_id' => $session->id])
                ->column();

            LiveSessionRankSnapshot::deleteAll(['live_session_id' => $session->id]);

            $sessionQuestionIds = LiveSessionQuestion::find()
                ->select('id')
                ->where(['live_session_id' => $session->id])
                ->column();

            Yii::$app->db->createCommand()
                ->delete('log', ['live_session_id' => $session->id])
                ->execute();

            if (!empty($submissionIds)) {
                Submission::deleteAll(['id' => $submissionIds]);
            }

            LiveSessionSubmission::deleteAll(['live_session_id' => $session->id]);

            if (!empty($sessionQuestionIds)) {
                LiveSessionQuestion::deleteAll(['id' => $sessionQuestionIds]);
            }

            $session->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Live session deleted.');
        } catch (\Throwable $throwable) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'The live session could not be deleted.');
        }

        return $this->redirect(['index']);
    }

    private function findSession(int $id): LiveSession
    {
        $session = LiveSession::find()->with('quiz')->where(['id' => $id])->one();
        if ($session === null) {
            throw new NotFoundHttpException('Live session not found.');
        }

        return $session;
    }
}
