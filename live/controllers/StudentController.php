<?php

namespace app\live\controllers;

use app\live\models\LiveSession;
use app\live\models\LiveSessionQuestion;
use app\live\models\LiveSessionSubmission;
use app\live\services\LiveLeaderboardService;
use app\live\services\LiveSessionManager;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class StudentController extends Controller
{
    private const LIVE_TOKEN_COOKIE = 'live_token';
    private const LIVE_CODE_COOKIE = 'live_code';

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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'join' => ['POST'],
                    'submit-answer' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(?string $code = null)
    {
        $this->layout = false;
        return $this->render('index', [
            'code' => strtoupper(trim((string)$code)),
        ]);
    }

    public function actionJoin()
    {
        $request = Yii::$app->request;
        $code = strtoupper(trim((string)$request->post('join_code')));
        $firstName = trim((string)$request->post('first_name'));
        $lastName = trim((string)$request->post('last_name'));
        $class = trim((string)$request->post('class'));

        $session = $this->findSessionByCode($code);
        if ($session->status !== LiveSession::STATUS_LOBBY) {
            Yii::$app->session->setFlash('error', 'This live session is no longer accepting new players.');
            return $this->redirect(['index', 'code' => $code]);
        }

        if ($firstName === '' || $lastName === '' || $class === '') {
            Yii::$app->session->setFlash('error', 'Please fill in your first name, last name, and class.');
            return $this->redirect(['index', 'code' => $code]);
        }

        $submission = $this->createLiveSubmission($session, [
            'first_name' => ucfirst($firstName),
            'last_name' => ucfirst($lastName),
            'class' => strtoupper($class),
        ]);

        Yii::$app->response->cookies->add(new Cookie([
            'name' => self::LIVE_TOKEN_COOKIE,
            'value' => $submission['token'],
            'expire' => time() + 3600 * 6,
        ]));
        Yii::$app->response->cookies->add(new Cookie([
            'name' => self::LIVE_CODE_COOKIE,
            'value' => $code,
            'expire' => time() + 3600 * 6,
        ]));

        return $this->redirect(['play', 'code' => $code]);
    }

    public function actionPlay(string $code)
    {
        $session = $this->findSessionByCode($code);
        $submission = $this->getJoinedSubmission($session);

        if ($submission === null) {
            return $this->redirect(['index', 'code' => $code]);
        }

        $this->layout = false;
        return $this->render('play', [
            'session' => $session,
            'submission' => $submission,
        ]);
    }

    public function actionState(string $code): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = $this->findSessionByCode($code);
        $submission = $this->getJoinedSubmission($session);
        if ($submission === null) {
            return ['ok' => false, 'redirect' => Yii::$app->urlManager->createUrl(['/live/student/index', 'code' => $code])];
        }

        $currentQuestion = $this->sessionManager->getCurrentSessionQuestion($session);
        $questionPayload = null;
        $answeredCurrentQuestion = false;
        $selectedAnswerNo = null;

        if ($currentQuestion !== null) {
            $questionPayload = $this->buildQuestionPayload($currentQuestion);
            $selectedAnswerNo = (new Query())
                ->from('log')
                ->select('answer_no')
                ->where([
                    'submission_id' => $submission['id'],
                    'live_session_question_id' => $currentQuestion->id,
                ])
                ->scalar();
            $answeredCurrentQuestion = $selectedAnswerNo !== false && $selectedAnswerNo !== null;
        }

        $presentation = $this->leaderboardService->buildPresentationData($session, $currentQuestion);
        $myRank = null;
        $myScore = 0;
        foreach ($presentation['leaderboard'] as $entry) {
            if ((int)$entry['submission_id'] === (int)$submission['id']) {
                $myRank = $entry['rank'];
                $myScore = (int)$entry['score'];
                break;
            }
        }

        return [
            'ok' => true,
            'session' => [
                'id' => (int)$session->id,
                'status' => $session->status,
                'currentQuestionIndex' => (int)$session->current_question_index,
                'questionCount' => (int)$session->question_count,
            ],
            'player' => [
                'name' => trim((string)$submission['first_name'] . ' ' . (string)$submission['last_name']),
                'score' => $myScore,
                'rank' => $myRank,
            ],
            'question' => $questionPayload,
            'answeredCurrentQuestion' => $answeredCurrentQuestion,
            'selectedAnswerNo' => $selectedAnswerNo !== null && $selectedAnswerNo !== false ? (int)$selectedAnswerNo : null,
            'top' => $presentation['top'],
        ];
    }

    public function actionSubmitAnswer(string $code): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = $this->findSessionByCode($code);
        $submission = $this->getJoinedSubmission($session);
        if ($submission === null) {
            return ['ok' => false, 'message' => 'Join the live session first.'];
        }
        if ($session->status !== LiveSession::STATUS_QUESTION_OPEN) {
            return ['ok' => false, 'message' => 'The question is not open right now.'];
        }

        $sessionQuestion = $this->sessionManager->getCurrentSessionQuestion($session);
        if ($sessionQuestion === null) {
            return ['ok' => false, 'message' => 'No active question was found.'];
        }

        $existing = (new Query())
            ->from('log')
            ->where([
                'submission_id' => $submission['id'],
                'live_session_question_id' => $sessionQuestion->id,
            ])
            ->exists();
        if ($existing) {
            return ['ok' => false, 'message' => 'You already answered this question.'];
        }

        $answerNo = (int)Yii::$app->request->post('answer_no');
        $question = (new Query())
            ->from('question')
            ->where(['id' => $sessionQuestion->question_id])
            ->one();

        if ($question === false || $question === null) {
            return ['ok' => false, 'message' => 'Question could not be found.'];
        }

        $maxAnswers = 0;
        for ($i = 1; $i <= 6; $i++) {
            if (trim((string)($question['a' . $i] ?? '')) !== '') {
                $maxAnswers = $i;
            }
        }

        if ($answerNo < 1 || $answerNo > $maxAnswers) {
            return ['ok' => false, 'message' => 'Invalid answer.'];
        }

        $isCorrect = ((int)$question['correct'] === $answerNo) ? 1 : 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->insertLogRow([
                'submission_id' => (int)$submission['id'],
                'quiz_id' => (int)$session->quiz_id,
                'question_id' => (int)$sessionQuestion->question_id,
                'answer_no' => $answerNo,
                'correct' => $isCorrect,
                'no_answered' => (int)$sessionQuestion->question_order,
                'live_session_id' => (int)$session->id,
                'live_session_question_id' => (int)$sessionQuestion->id,
            ]);

            $answerOrder = $this->replaceAnswerAtPosition((string)$submission['answer_order'], (int)$sessionQuestion->question_order - 1, $answerNo);
            Yii::$app->db->createCommand()->update('submission', [
                'no_answered' => (int)$submission['no_answered'] + 1,
                'no_correct' => (int)$submission['no_correct'] + $isCorrect,
                'answer_order' => $answerOrder,
            ], ['id' => $submission['id']])->execute();

            $transaction->commit();
        } catch (\Throwable $throwable) {
            $transaction->rollBack();
            return ['ok' => false, 'message' => 'Your answer could not be saved.'];
        }

        return ['ok' => true];
    }

    private function findSessionByCode(string $code): LiveSession
    {
        $session = LiveSession::find()->where(['join_code' => strtoupper(trim($code))])->one();
        if ($session === null) {
            throw new NotFoundHttpException('Live session not found.');
        }

        return $session;
    }

    private function getJoinedSubmission(LiveSession $session): ?array
    {
        $cookies = Yii::$app->request->cookies;
        $token = $cookies->getValue(self::LIVE_TOKEN_COOKIE);
        $code = strtoupper((string)$cookies->getValue(self::LIVE_CODE_COOKIE));

        if (!$token || $code !== strtoupper($session->join_code)) {
            return null;
        }

        $submission = (new Query())
            ->select(['s.*'])
            ->from(['lss' => 'live_session_submission'])
            ->innerJoin(['s' => 'submission'], 's.id = lss.submission_id')
            ->where([
                'lss.live_session_id' => $session->id,
                's.token' => $token,
            ])
            ->one();

        return $submission ?: null;
    }

    private function createLiveSubmission(LiveSession $session, array $payload): array
    {
        $schema = Yii::$app->db->schema->getTableSchema('submission');
        if ($schema === null) {
            throw new Exception('Submission table schema could not be loaded.');
        }

        $now = date('Y-m-d H:i:s');
        $questionOrder = $this->sessionManager->buildQuestionOrderString((int)$session->id);
        $answerOrder = $this->sessionManager->buildBlankAnswerOrder((int)$session->question_count);
        $token = bin2hex(random_bytes(16));
        $columns = [
            'token' => $token,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'class' => $payload['class'],
            'start_time' => $now,
            'ip_address' => (string)Yii::$app->request->userIP,
            'question_order' => $questionOrder,
            'no_questions' => (int)$session->question_count,
            'no_answered' => 0,
            'no_correct' => 0,
            'finished' => 0,
            'quiz_id' => (int)$session->quiz_id,
            'answer_order' => $answerOrder,
        ];

        if (isset($schema->columns['student_nr'])) {
            $columns['student_nr'] = null;
        }
        if (isset($schema->columns['user_agent'])) {
            $columns['user_agent'] = substr((string)Yii::$app->request->userAgent, 0, 200);
        }
        if (isset($schema->columns['last_updated'])) {
            $columns['last_updated'] = $now;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->insert('submission', $columns)->execute();
            $submissionId = (int)Yii::$app->db->getLastInsertID();

            $link = new LiveSessionSubmission([
                'live_session_id' => (int)$session->id,
                'submission_id' => $submissionId,
            ]);
            $link->save(false);

            $transaction->commit();
        } catch (\Throwable $throwable) {
            $transaction->rollBack();
            throw $throwable;
        }

        return (new Query())
            ->from('submission')
            ->where(['id' => $submissionId])
            ->one();
    }

    private function buildQuestionPayload(LiveSessionQuestion $sessionQuestion): array
    {
        $question = (new Query())
            ->from('question')
            ->where(['id' => $sessionQuestion->question_id])
            ->one();

        $answers = $this->buildShuffledAnswers($sessionQuestion, $question);

        return [
            'id' => (int)$question['id'],
            'order' => (int)$sessionQuestion->question_order,
            'text' => (string)$question['question'],
            'answers' => $answers,
        ];
    }

    private function buildShuffledAnswers(LiveSessionQuestion $sessionQuestion, array $question): array
    {
        $answers = [];
        for ($i = 1; $i <= 6; $i++) {
            $value = trim((string)($question['a' . $i] ?? ''));
            if ($value !== '') {
                $answers[] = [
                    'answer_no' => $i,
                    'label' => $value,
                    'sort_key' => hash('sha256', $sessionQuestion->id . ':' . $question['id'] . ':' . $i),
                ];
            }
        }

        usort($answers, static function (array $left, array $right): int {
            return strcmp($left['sort_key'], $right['sort_key']);
        });

        foreach ($answers as $index => &$answer) {
            $answer['display_no'] = $index + 1;
            unset($answer['sort_key']);
        }
        unset($answer);

        return $answers;
    }

    private function replaceAnswerAtPosition(string $answerOrder, int $index, int $answerNo): string
    {
        $answers = preg_split('/\s+/', trim($answerOrder)) ?: [];
        if ($index < 0) {
            return $answerOrder;
        }
        while (count($answers) <= $index) {
            $answers[] = '0';
        }
        $answers[$index] = (string)$answerNo;
        return implode(' ', $answers);
    }

    private function insertLogRow(array $columns): void
    {
        $schema = Yii::$app->db->schema->getTableSchema('log');
        if ($schema === null) {
            throw new Exception('Log table schema could not be loaded.');
        }

        $insert = [];
        foreach ($columns as $name => $value) {
            if (isset($schema->columns[$name])) {
                $insert[$name] = $value;
            }
        }

        Yii::$app->db->createCommand()->insert('log', $insert)->execute();
    }
}
