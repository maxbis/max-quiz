<?php

use app\models\Question;
use app\models\Quiz;
use app\models\Quizquestion;
use app\models\Submission;

class QuizLabelGroupOrderCest
{
    private const FIRST_NAME = 'Label';
    private const LAST_NAME = 'Group';
    private const CLASS_CODE = '9B';
    private const QUIZ_PASSWORD = 'label-group-test';
    private const QUIZ_NAME = 'Classic Label Group Test';

    public function _before(\FunctionalTester $I)
    {
        $this->cleanup();
    }

    public function _after(\FunctionalTester $I)
    {
        $this->cleanup();
    }

    public function classicQuizStartUsesRandomLabelGroups(\FunctionalTester $I)
    {
        $quiz = new Quiz([
            'name' => self::QUIZ_NAME,
            'quiz_group' => self::QUIZ_NAME,
            'password' => self::QUIZ_PASSWORD,
            'active' => 1,
            'review' => 0,
            'blind' => 0,
            'ip_check' => 0,
            'random' => Quiz::ORDER_MODE_RANDOM_LABEL_GROUPS,
        ]);
        $I->assertTrue($quiz->save(false));

        $questionSpecs = [
            ['id' => 930201, 'label' => 'A', 'order' => 1],
            ['id' => 930202, 'label' => 'A', 'order' => 2],
            ['id' => 930203, 'label' => 'B', 'order' => 3],
            ['id' => 930204, 'label' => 'B', 'order' => 4],
            ['id' => 930205, 'label' => 'C', 'order' => 5],
            ['id' => 930206, 'label' => 'C', 'order' => 6],
        ];

        foreach ($questionSpecs as $spec) {
            $question = new Question([
                'id' => $spec['id'],
                'question' => 'Question ' . $spec['id'],
                'a1' => 'A',
                'a2' => 'B',
                'correct' => 1,
                'label' => $spec['label'],
            ]);
            $I->assertTrue($question->save(false));

            $quizQuestion = new Quizquestion([
                'quiz_id' => $quiz->id,
                'question_id' => $spec['id'],
                'active' => 1,
                'order' => $spec['order'],
            ]);
            $I->assertTrue($quizQuestion->save(false));
        }

        $I->amOnRoute('submission/create');
        $I->submitForm('form#form', [
            'first_name' => self::FIRST_NAME,
            'last_name' => self::LAST_NAME,
            'student_nr' => '91001',
            'class' => self::CLASS_CODE,
            'password' => self::QUIZ_PASSWORD,
        ], 'Start Quiz');

        $I->seeInCurrentUrl('site%2Fquestion');

        $submission = Submission::find()
            ->where([
                'first_name' => self::FIRST_NAME,
                'last_name' => self::LAST_NAME,
                'quiz_id' => $quiz->id,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $I->assertNotNull($submission);

        $orderedIds = array_values(array_filter(explode(' ', trim((string) $submission->question_order))));
        $orderedIds = array_map('intval', $orderedIds);

        $expectedOrders = [
            [930201, 930202, 930203, 930204, 930205, 930206],
            [930201, 930202, 930205, 930206, 930203, 930204],
            [930203, 930204, 930201, 930202, 930205, 930206],
            [930203, 930204, 930205, 930206, 930201, 930202],
            [930205, 930206, 930201, 930202, 930203, 930204],
            [930205, 930206, 930203, 930204, 930201, 930202],
        ];

        $I->assertContains($orderedIds, $expectedOrders);
    }

    private function cleanup(): void
    {
        $quiz = Quiz::find()->where(['password' => self::QUIZ_PASSWORD])->one();
        if ($quiz === null) {
            return;
        }

        $submissionIds = Submission::find()
            ->select('id')
            ->where(['quiz_id' => $quiz->id])
            ->column();

        if (!empty($submissionIds)) {
            Yii::$app->db->createCommand()->delete('log', ['submission_id' => $submissionIds])->execute();
            Submission::deleteAll(['id' => $submissionIds]);
        }

        $questionIds = Quizquestion::find()
            ->select('question_id')
            ->where(['quiz_id' => $quiz->id])
            ->column();

        Quizquestion::deleteAll(['quiz_id' => $quiz->id]);

        if (!empty($questionIds)) {
            Question::deleteAll(['id' => $questionIds]);
        }

        Quiz::deleteAll(['id' => $quiz->id]);
    }
}
