<?php

namespace app\controllers;

use app\models\Question;
use app\models\Quiz;
use app\models\Submission;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;

class MaintenanceController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'invalidate-question', 'backups', 'restore'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($quiz_id = null)
    {
        $quizRows = Quiz::find()
            ->select(['id', 'name', 'archived'])
            ->orderBy(['archived' => SORT_ASC, 'name' => SORT_ASC])
            ->asArray()
            ->all();

        $quizOptions = [];
        foreach ($quizRows as $row) {
            $label = $row['name'];
            if ((int)$row['archived'] === 1) {
                $label .= ' (archived)';
            }
            $quizOptions[$row['id']] = $label;
        }

        $selectedQuiz = null;
        $questionOptions = [];
        if ($quiz_id !== null && $quiz_id !== '') {
            $selectedQuiz = Quiz::findOne((int)$quiz_id);
            if ($selectedQuiz) {
                $questionRows = Yii::$app->db->createCommand(
                    'SELECT q.id, q.question
                     FROM question q
                     JOIN quizquestion qq ON qq.question_id = q.id
                     WHERE qq.quiz_id = :quiz AND qq.active = 1
                     ORDER BY COALESCE(qq.order, 0) ASC, q.id ASC',
                    [':quiz' => (int)$quiz_id]
                )->queryAll();

                foreach ($questionRows as $row) {
                    $text = trim(strip_tags($row['question']));
                    if ($text === '') {
                        $text = '(no text)';
                    }
                    if (mb_strlen($text) > 80) {
                        $text = mb_substr($text, 0, 80) . '…';
                    }
                    $questionOptions[$row['id']] = sprintf('#%d %s', $row['id'], $text);
                }
            }
        }

        return $this->render('index', [
            'quizOptions' => $quizOptions,
            'selectedQuiz' => $selectedQuiz,
            'questionOptions' => $questionOptions,
            'selectedQuizId' => $quiz_id,
        ]);
    }

    public function actionBackups($quiz_id = null)
    {
        $this->ensureBackupTables();

        $params = [];
        $where = '';
        if ($quiz_id !== null && $quiz_id !== '') {
            $where = 'WHERE quiz_id = :quiz_id';
            $params[':quiz_id'] = (int)$quiz_id;
        }

        $sql = <<<SQL
            SELECT batch_key,
                   quiz_id,
                   question_id,
                   COUNT(*) as submission_count,
                   MIN(created_at) as created_at,
                   MAX(note) as note
            FROM submission_backup
            $where
            GROUP BY batch_key, quiz_id, question_id
            ORDER BY created_at DESC
        SQL;

        $backups = Yii::$app->db->createCommand($sql, $params)->queryAll();

        return $this->render('backups', [
            'backups' => $backups,
            'quizId' => $quiz_id,
        ]);
    }

    public function actionInvalidateQuestion($quiz_id, $question_id)
    {
        $quiz = Quiz::findOne((int)$quiz_id);
        if (!$quiz) {
            throw new NotFoundHttpException("Quiz $quiz_id not found.");
        }

        $question = Question::findOne((int)$question_id);
        if (!$question) {
            throw new NotFoundHttpException("Question $question_id not found.");
        }

        $submissions = Submission::find()
            ->where(['quiz_id' => $quiz->id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $affected = $this->collectAffectedSubmissions($submissions, (int)$question_id);
        $answeredCount = 0;
        foreach ($affected as $item) {
            if ($item['log']) {
                ++$answeredCount;
            }
        }

        $request = Yii::$app->request;
        $result = null;
        if ($request->isPost) {
            $batchKey = $this->createBatchKey($quiz->id, (int)$question_id);
            $note = sprintf('Invalidate question %d on quiz %d', $question->id, $quiz->id);

            $result = $this->invalidateWithBackup($batchKey, $note, $affected, $question->id);
            Yii::$app->session->setFlash('success', sprintf(
                'Invalidated question %d for quiz %s. Batch key: %s. Updated %d submissions.',
                $question->id,
                $quiz->name,
                $batchKey,
                $result['updated']
            ));
        }

        return $this->render('invalidate-question', [
            'quiz' => $quiz,
            'question' => $question,
            'affectedCount' => count($affected),
            'answeredCount' => $answeredCount,
            'result' => $result,
        ]);
    }

    public function actionRestore($batch_key)
    {
        $this->ensureBackupTables();
        $batchKey = trim((string)$batch_key);
        if ($batchKey === '') {
            throw new BadRequestHttpException('Missing batch key.');
        }

        $rows = Yii::$app->db->createCommand(
            'SELECT * FROM submission_backup WHERE batch_key = :batch ORDER BY id ASC',
            [':batch' => $batchKey]
        )->queryAll();

        if (!$rows) {
            throw new NotFoundHttpException('Backup batch not found.');
        }

        $request = Yii::$app->request;
        $result = null;
        if ($request->isPost) {
            $result = $this->restoreBatch($rows);
            Yii::$app->session->setFlash('success', sprintf(
                'Restored %d submissions and %d log entries from batch %s.',
                $result['submissions'],
                $result['logs'],
                $batchKey
            ));
        }

        return $this->render('restore', [
            'batchKey' => $batchKey,
            'rows' => $rows,
            'result' => $result,
        ]);
    }

    private function invalidateWithBackup(string $batchKey, string $note, array $affected, int $questionId): array
    {
        $this->ensureBackupTables();
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        $updated = 0;
        $logsRemoved = 0;
        try {
            foreach ($affected as $item) {
                /** @var Submission $submission */
                $submission = $item['submission'];

                $this->backupSubmission($submission, $questionId, $batchKey, $note, $item['log']);

                $questionOrder = $item['question_order'];
                $answerOrder = $item['answer_order'];

                $questionOrder = $this->removeIndex($questionOrder, $item['question_index']);
                $newQuestionOrder = $this->orderToString($questionOrder);

                $newNoQuestions = max(0, (int)$submission->no_questions - 1);
                $newNoAnswered = (int)$submission->no_answered;
                $newNoCorrect = (int)$submission->no_correct;

                if ($item['log']) {
                    $answerIndex = isset($item['log']['no_answered']) ? (int)$item['log']['no_answered'] : null;
                    $answerOrder = $this->removeIndex($answerOrder, $answerIndex);
                    $newNoAnswered = max(0, $newNoAnswered - 1);
                    if ((int)$item['log']['correct'] === 1) {
                        $newNoCorrect = max(0, $newNoCorrect - 1);
                    }

                    $logsRemoved += Yii::$app->db->createCommand()
                        ->delete('log', [
                            'submission_id' => $submission->id,
                            'question_id' => $questionId,
                        ])
                        ->execute();
                }

                $newAnswerOrder = $this->orderToString($answerOrder);

                $fields = [
                    'question_order' => $newQuestionOrder,
                    'answer_order' => $newAnswerOrder,
                    'no_questions' => $newNoQuestions,
                    'no_answered' => $newNoAnswered,
                    'no_correct' => $newNoCorrect,
                    'last_updated' => date('Y-m-d H:i:s'),
                ];

                Yii::$app->db->createCommand()
                    ->update('submission', $fields, ['id' => $submission->id])
                    ->execute();

                writeLog(sprintf(
                    'Invalidate question %d on submission %d (batch %s)',
                    $questionId,
                    $submission->id,
                    $batchKey
                ));

                ++$updated;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [
            'updated' => $updated,
            'logsRemoved' => $logsRemoved,
            'batchKey' => $batchKey,
        ];
    }

    private function restoreBatch(array $rows): array
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $submissionCount = 0;
        $logCount = 0;

        try {
            foreach ($rows as $row) {
                $payload = json_decode($row['payload'], true);
                $logPayload = json_decode($row['log_payload'], true) ?: [];

                if (!is_array($payload)) {
                    continue;
                }

                $submissionId = (int)$payload['id'];
                $columns = array_flip(Submission::getTableSchema()->columnNames);
                $payload = array_intersect_key($payload, $columns);

                // Restore submission row
                $exists = (bool)Submission::find()->where(['id' => $submissionId])->exists();
                if ($exists) {
                    $db->createCommand()->update('submission', $payload, ['id' => $submissionId])->execute();
                } else {
                    $db->createCommand()->insert('submission', $payload)->execute();
                }
                ++$submissionCount;

                // Restore logs for this submission/question
                $db->createCommand()->delete('log', [
                    'submission_id' => $submissionId,
                    'question_id' => $row['question_id'],
                ])->execute();

                foreach ($logPayload as $logRow) {
                    if (!is_array($logRow)) {
                        continue;
                    }
                    unset($logRow['id']);
                    $db->createCommand()->insert('log', $logRow)->execute();
                    ++$logCount;
                }

                writeLog(sprintf(
                    'Restore submission %d from batch %s',
                    $submissionId,
                    $row['batch_key']
                ));
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [
            'submissions' => $submissionCount,
            'logs' => $logCount,
        ];
    }

    private function backupSubmission(Submission $submission, int $questionId, string $batchKey, string $note, ?array $logRow): void
    {
        $payload = $this->extractSubmissionData($submission);

        Yii::$app->db->createCommand()->insert('submission_backup', [
            'batch_key' => $batchKey,
            'submission_id' => $submission->id,
            'quiz_id' => $submission->quiz_id,
            'question_id' => $questionId,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'log_payload' => json_encode($logRow ? [$logRow] : [], JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Yii::$app->user->id,
            'note' => $note,
        ])->execute();
    }

    private function extractSubmissionData(Submission $submission): array
    {
        $columns = array_flip(Submission::getTableSchema()->columnNames);
        $attributes = $submission->getAttributes();

        return array_intersect_key($attributes, $columns);
    }

    private function collectAffectedSubmissions(array $submissions, int $questionId): array
    {
        $affected = [];
        foreach ($submissions as $submission) {
            $questionOrder = $this->stringToOrder($submission->question_order);
            $questionIndex = array_search((string)$questionId, $questionOrder, true);
            if ($questionIndex === false) {
                continue;
            }

            $answerOrder = $this->stringToOrder($submission->answer_order);
            $logRow = $this->findLogRow($submission->id, $questionId);

            $affected[] = [
                'submission' => $submission,
                'question_index' => $questionIndex,
                'question_order' => $questionOrder,
                'answer_order' => $answerOrder,
                'log' => $logRow,
            ];
        }

        return $affected;
    }

    private function findLogRow(int $submissionId, int $questionId): ?array
    {
        $sql = 'SELECT * FROM log WHERE submission_id = :sid AND question_id = :qid ORDER BY id DESC LIMIT 1';
        $row = Yii::$app->db->createCommand($sql, [
            ':sid' => $submissionId,
            ':qid' => $questionId,
        ])->queryOne();

        return $row ?: null;
    }

    private function stringToOrder(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        $value = trim(preg_replace('/\s+/', ' ', (string)$value));
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $value), static fn($item) => $item !== ''));
    }

    private function orderToString(array $items): string
    {
        $items = array_values(array_filter($items, static fn($item) => $item !== '' && $item !== null));
        if (empty($items)) {
            return '';
        }

        return implode(' ', $items);
    }

    private function removeIndex(array $items, ?int $index): array
    {
        if ($index === null) {
            return $items;
        }

        if (!array_key_exists($index, $items)) {
            return $items;
        }

        unset($items[$index]);

        return array_values($items);
    }

    private function createBatchKey(int $quizId, int $questionId): string
    {
        return sprintf('q%d-%d-%s', $quizId, $questionId, bin2hex(random_bytes(6)));
    }

    private function ensureBackupTables(): void
    {
        $sqlSubmission = <<<SQL
            CREATE TABLE IF NOT EXISTS `submission_backup` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `batch_key` VARCHAR(64) NOT NULL,
                `submission_id` INT NOT NULL,
                `quiz_id` INT NOT NULL,
                `question_id` INT DEFAULT NULL,
                `payload` LONGTEXT NOT NULL,
                `log_payload` LONGTEXT,
                `created_at` DATETIME NOT NULL,
                `created_by` INT DEFAULT NULL,
                `note` VARCHAR(255) DEFAULT NULL,
                INDEX (`batch_key`),
                INDEX (`quiz_id`),
                INDEX (`submission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL;

        Yii::$app->db->createCommand($sqlSubmission)->execute();
    }
}


