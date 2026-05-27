<?php

use app\models\Quiz;
use app\models\Submission;
use yii\db\Query;

class ClassicQuizFlowCest
{
    private const FIRST_NAME = 'Regression';
    private const LAST_NAME = 'ClassicFlow';
    private const CLASS_CODE = '9A';

    public function _before(\FunctionalTester $I)
    {
        $this->cleanupRegressionRows();
    }

    public function _after(\FunctionalTester $I)
    {
        $this->cleanupRegressionRows();
    }

    public function originalSelfPacedQuizFlowStillWorks(\FunctionalTester $I)
    {
        $I->haveServerParameter('REMOTE_ADDR', '127.0.0.1');

        $quiz = Quiz::find()
            ->where([
                'name' => 'Test',
                'password' => 'test',
                'active' => 1,
                'archived' => 0,
            ])
            ->one();

        $I->assertNotNull($quiz, 'Expected the seeded Test quiz to exist and be active.');

        $I->amOnRoute('submission/create');
        $I->see('Start Quiz');

        $studentNumber = (string) random_int(9000, 9999);

        $I->submitForm('form#form', [
            'first_name' => self::FIRST_NAME,
            'last_name' => self::LAST_NAME,
            'student_nr' => $studentNumber,
            'class' => self::CLASS_CODE,
            'password' => 'test',
        ]);

        $I->seeInCurrentUrl('site%2Fquestion');

        $submission = Submission::find()
            ->where([
                'first_name' => self::FIRST_NAME,
                'last_name' => self::LAST_NAME,
                'quiz_id' => $quiz->id,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $I->assertNotNull($submission, 'Starting the classic quiz should create a submission row.');

        $questionIds = array_values(array_filter(explode(' ', trim((string) $submission->question_order))));
        $I->assertNotEmpty($questionIds, 'The classic submission should contain at least one question.');

        foreach ($questionIds as $index => $questionId) {
            $correctAnswer = (new Query())
                ->from('question')
                ->where(['id' => (int) $questionId])
                ->select('correct')
                ->scalar();

            $I->assertNotEmpty($correctAnswer, sprintf('Question %d should have a correct answer value.', (int) $questionId));

            $I->submitForm('form#answer', [
                'selectedAnswer' => (string) $correctAnswer,
                'no_answered' => (string) $index,
            ]);

            if ($index < count($questionIds) - 1) {
                $I->seeInCurrentUrl('site%2Fquestion');
            }
        }

        $I->seeInCurrentUrl('site%2Ffinished');
        $I->see('Results');

        $submission->refresh();

        $I->assertSame(count($questionIds), (int) $submission->no_questions);
        $I->assertSame(count($questionIds), (int) $submission->no_answered);
        $I->assertSame(count($questionIds), (int) $submission->no_correct);
        $I->assertSame(1, (int) $submission->finished);
        $I->assertNotEmpty($submission->end_time, 'The classic quiz flow should set an end time when finished.');

        $logs = (new Query())
            ->from('log')
            ->where(['submission_id' => $submission->id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $I->assertCount(count($questionIds), $logs);

        foreach ($logs as $logRow) {
            $I->assertSame(1, (int) $logRow['correct']);
            $I->assertArrayHasKey('live_session_id', $logRow);
            $I->assertArrayHasKey('live_session_question_id', $logRow);
            $I->assertNull($logRow['live_session_id']);
            $I->assertNull($logRow['live_session_question_id']);
        }

        $I->amOnRoute('site/results', ['token' => $submission->token]);
        $I->see('Results for ' . self::FIRST_NAME . ' ' . self::LAST_NAME);
        $I->see((string) count($questionIds));
        $I->see('100');
    }

    private function cleanupRegressionRows(): void
    {
        $submissionIds = Submission::find()
            ->select('id')
            ->where([
                'first_name' => self::FIRST_NAME,
                'last_name' => self::LAST_NAME,
                'class' => self::CLASS_CODE,
            ])
            ->column();

        if (!empty($submissionIds)) {
            Yii::$app->db->createCommand()
                ->delete('log', ['submission_id' => $submissionIds])
                ->execute();

            Submission::deleteAll(['id' => $submissionIds]);
        }
    }
}
