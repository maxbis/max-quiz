<?php

namespace app\live\controllers;

use app\live\models\LiveSession;
use app\live\services\LiveLeaderboardService;
use app\live\services\LiveSessionManager;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ScreenController extends Controller
{
    private LiveSessionManager $sessionManager;
    private LiveLeaderboardService $leaderboardService;

    public function init()
    {
        parent::init();
        $this->sessionManager = new LiveSessionManager();
        $this->leaderboardService = new LiveLeaderboardService();
    }

    public function actionView(string $code)
    {
        $session = $this->findSessionByCode($code);
        $this->layout = false;
        return $this->render('view', [
            'session' => $session,
        ]);
    }

    public function actionState(string $code): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $session = $this->findSessionByCode($code);
        $currentQuestion = $this->sessionManager->getCurrentSessionQuestion($session);
        $question = null;
        $correctAnswer = null;
        $answerCount = 0;

        if ($currentQuestion !== null) {
            $row = (new Query())
                ->from('question')
                ->where(['id' => $currentQuestion->question_id])
                ->one();

            if ($row) {
                $answers = [];
                for ($i = 1; $i <= 6; $i++) {
                    $value = trim((string)($row['a' . $i] ?? ''));
                    if ($value !== '') {
                        $answers[] = [
                            'answer_no' => $i,
                            'label' => $value,
                        ];
                    }
                }

                $question = [
                    'order' => (int)$currentQuestion->question_order,
                    'text' => (string)$row['question'],
                    'answers' => $answers,
                    'correctAnswerNo' => (int)$row['correct'],
                ];
                $correctAnswer = [
                    'answer_no' => (int)$row['correct'],
                    'label' => (string)($row['a' . (int)$row['correct']] ?? ''),
                ];
            }

            $answerCount = (int)(new Query())
                ->from('log')
                ->where([
                    'live_session_id' => $session->id,
                    'live_session_question_id' => $currentQuestion->id,
                ])
                ->count('*');
        }

        $presentation = $this->leaderboardService->buildPresentationData($session, $currentQuestion);

        return [
            'ok' => true,
            'session' => [
                'status' => $session->status,
                'joinCode' => $session->join_code,
                'currentQuestionIndex' => (int)$session->current_question_index,
                'questionCount' => (int)$session->question_count,
                'quizName' => $session->quiz ? $session->quiz->name : 'Live Quiz',
            ],
            'question' => $question,
            'correctAnswer' => $correctAnswer,
            'answerCount' => $answerCount,
            'top' => $presentation['top'],
            'movers' => $presentation['movers'],
            'totalPlayers' => $presentation['totalPlayers'],
        ];
    }

    private function findSessionByCode(string $code): LiveSession
    {
        $session = LiveSession::find()->with('quiz')->where(['join_code' => strtoupper(trim($code))])->one();
        if ($session === null) {
            throw new NotFoundHttpException('Live session not found.');
        }

        return $session;
    }
}
