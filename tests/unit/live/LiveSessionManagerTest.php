<?php

namespace tests\unit\live;

use app\live\models\LiveSessionQuestion;
use app\live\services\LiveSessionManager;
use app\models\Question;
use app\models\Quiz;
use app\models\Quizquestion;
use Yii;
use Codeception\Test\Unit;

class LiveSessionManagerTest extends Unit
{
    private const QUIZ_PASSWORD = 'label-live';
    private const QUIZ_NAME = 'Live Label Group Test';

    protected function _after(): void
    {
        $quiz = Quiz::find()->where(['password' => self::QUIZ_PASSWORD])->one();
        if ($quiz === null) {
            return;
        }

        $questionIds = Quizquestion::find()
            ->select('question_id')
            ->where(['quiz_id' => $quiz->id])
            ->column();

        LiveSessionQuestion::deleteAll(['live_session_id' => Yii::$app->db->createCommand(
            'SELECT id FROM live_session WHERE quiz_id = :quiz_id',
            [':quiz_id' => $quiz->id]
        )->queryColumn()]);
        Yii::$app->db->createCommand()->delete('live_session', ['quiz_id' => $quiz->id])->execute();
        Quizquestion::deleteAll(['quiz_id' => $quiz->id]);
        if (!empty($questionIds)) {
            Question::deleteAll(['id' => $questionIds]);
        }
        Quiz::deleteAll(['id' => $quiz->id]);
    }

    public function testCreateSessionKeepsLabelGroupsTogether(): void
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
        $this->assertTrue($quiz->save(false));

        $questionSpecs = [
            ['id' => 930101, 'label' => 'A', 'order' => 1],
            ['id' => 930102, 'label' => 'A', 'order' => 2],
            ['id' => 930103, 'label' => 'B', 'order' => 3],
            ['id' => 930104, 'label' => 'B', 'order' => 4],
            ['id' => 930105, 'label' => 'C', 'order' => 5],
            ['id' => 930106, 'label' => 'C', 'order' => 6],
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
            $this->assertTrue($question->save(false));

            $quizQuestion = new Quizquestion([
                'quiz_id' => $quiz->id,
                'question_id' => $spec['id'],
                'active' => 1,
                'order' => $spec['order'],
            ]);
            $this->assertTrue($quizQuestion->save(false));
        }

        $manager = new LiveSessionManager();
        $session = $manager->createSession((int) $quiz->id);

        $orderedIds = LiveSessionQuestion::find()
            ->select('question_id')
            ->where(['live_session_id' => $session->id])
            ->orderBy(['question_order' => SORT_ASC])
            ->column();

        $orderedIds = array_map('intval', $orderedIds);

        $expectedOrders = [
            [930101, 930102, 930103, 930104, 930105, 930106],
            [930101, 930102, 930105, 930106, 930103, 930104],
            [930103, 930104, 930101, 930102, 930105, 930106],
            [930103, 930104, 930105, 930106, 930101, 930102],
            [930105, 930106, 930101, 930102, 930103, 930104],
            [930105, 930106, 930103, 930104, 930101, 930102],
        ];

        $this->assertContains($orderedIds, $expectedOrders);
    }
}
