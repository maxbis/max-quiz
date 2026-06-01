<?php

namespace app\live\services;

use app\live\models\LiveSession;
use app\live\models\LiveSessionQuestion;
use app\live\models\LiveSessionSubmission;
use app\models\Quiz;
use app\services\QuizQuestionOrderService;
use Yii;
use yii\db\Exception;

class LiveSessionManager
{
    public function createSession(int $quizId, ?int $userId = null, string $scoringMode = LiveSession::SCORING_MODE_CORRECT_DIFFICULTY_BONUS): LiveSession
    {
        $quiz = Quiz::findOne($quizId);
        if ($quiz === null) {
            throw new Exception('Quiz not found.');
        }

        if (!array_key_exists($scoringMode, LiveSession::scoringModeOptions())) {
            throw new Exception('Invalid scoring mode.');
        }

        $questionIds = (new QuizQuestionOrderService())->buildQuestionIdsForQuiz(
            $quizId,
            (int) $quiz->random
        );

        if (empty($questionIds)) {
            throw new Exception('Selected quiz has no active questions.');
        }

        $questionIds = array_map('intval', $questionIds);

        $maxQuestions = (int)$quiz->no_questions;
        if ($maxQuestions > 0 && count($questionIds) > $maxQuestions) {
            $questionIds = array_slice($questionIds, 0, $maxQuestions);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $session = new LiveSession([
                'quiz_id' => $quizId,
                'join_code' => $this->generateJoinCode(),
                'status' => LiveSession::STATUS_LOBBY,
                'scoring_mode' => $scoringMode,
                'current_question_index' => 0,
                'question_count' => count($questionIds),
                'created_by_user_id' => $userId,
            ]);
            $session->save(false);

            foreach (array_values($questionIds) as $index => $questionId) {
                $sessionQuestion = new LiveSessionQuestion([
                    'live_session_id' => $session->id,
                    'question_id' => (int)$questionId,
                    'question_order' => $index + 1,
                ]);
                $sessionQuestion->save(false);
            }

            $transaction->commit();
            return $session;
        } catch (\Throwable $throwable) {
            $transaction->rollBack();
            throw $throwable;
        }
    }

    public function openNextQuestion(LiveSession $session): void
    {
        if ($session->status === LiveSession::STATUS_FINISHED) {
            throw new Exception('Session is already finished.');
        }

        $nextIndex = $session->current_question_index + 1;
        if ($nextIndex > (int)$session->question_count) {
            throw new Exception('There are no more questions in this session.');
        }

        $sessionQuestion = $this->getSessionQuestionByOrder($session->id, $nextIndex);
        if ($sessionQuestion === null) {
            throw new Exception('Next session question could not be found.');
        }

        if ($session->started_at === null) {
            $session->started_at = date('Y-m-d H:i:s');
        }
        $session->current_question_index = $nextIndex;
        $session->status = LiveSession::STATUS_QUESTION_OPEN;
        $session->save(false);

        $sessionQuestion->opened_at = date('Y-m-d H:i:s');
        $sessionQuestion->save(false);
    }

    public function closeCurrentQuestion(LiveSession $session, LiveLeaderboardService $leaderboardService): array
    {
        if ($session->status !== LiveSession::STATUS_QUESTION_OPEN) {
            throw new Exception('A question must be open before showing the leaderboard.');
        }

        $sessionQuestion = $this->getCurrentSessionQuestion($session);
        if ($sessionQuestion === null) {
            throw new Exception('Current session question could not be found.');
        }

        $sessionQuestion->closed_at = date('Y-m-d H:i:s');
        $sessionQuestion->save(false);

        $session->status = LiveSession::STATUS_LEADERBOARD;
        $session->save(false);

        return $leaderboardService->snapshotCurrentLeaderboard($session, $sessionQuestion);
    }

    public function finishSession(LiveSession $session): void
    {
        $session->status = LiveSession::STATUS_FINISHED;
        $session->ended_at = date('Y-m-d H:i:s');
        $session->save(false);

        $submissionIds = LiveSessionSubmission::find()
            ->select('submission_id')
            ->where(['live_session_id' => $session->id])
            ->column();

        if (!empty($submissionIds)) {
            Yii::$app->db->createCommand()->update('submission', [
                'finished' => 1,
                'end_time' => date('Y-m-d H:i:s'),
            ], ['id' => $submissionIds])->execute();
        }
    }

    public function getCurrentSessionQuestion(LiveSession $session): ?LiveSessionQuestion
    {
        if ((int)$session->current_question_index <= 0) {
            return null;
        }

        return $this->getSessionQuestionByOrder($session->id, (int)$session->current_question_index);
    }

    public function getSessionQuestionByOrder(int $sessionId, int $order): ?LiveSessionQuestion
    {
        return LiveSessionQuestion::findOne([
            'live_session_id' => $sessionId,
            'question_order' => $order,
        ]);
    }

    public function generateJoinCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (LiveSession::find()->where(['join_code' => $code])->exists());

        return $code;
    }

    public function buildQuestionOrderString(int $sessionId): string
    {
        $questionIds = LiveSessionQuestion::find()
            ->select('question_id')
            ->where(['live_session_id' => $sessionId])
            ->orderBy(['question_order' => SORT_ASC])
            ->column();

        return implode(' ', array_map('intval', $questionIds));
    }

    public function buildBlankAnswerOrder(int $count): string
    {
        return implode(' ', array_fill(0, $count, '0'));
    }
}
