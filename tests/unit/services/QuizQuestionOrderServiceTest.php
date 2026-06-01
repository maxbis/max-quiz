<?php

namespace tests\unit\services;

use app\models\Quiz;
use app\services\QuizQuestionOrderService;
use Codeception\Test\Unit;

class QuizQuestionOrderServiceTest extends Unit
{
    public function testFixedModeKeepsOriginalOrder(): void
    {
        $service = new QuizQuestionOrderService();

        $rows = [
            ['question_id' => 1, 'label' => 'A'],
            ['question_id' => 2, 'label' => 'A'],
            ['question_id' => 5, 'label' => 'B'],
        ];

        $this->assertSame([1, 2, 5], $service->orderRows($rows, Quiz::ORDER_MODE_FIXED));
    }

    public function testRandomModeShufflesIndividualQuestions(): void
    {
        $service = new QuizQuestionOrderService();

        $rows = [
            ['question_id' => 1, 'label' => 'A'],
            ['question_id' => 2, 'label' => 'A'],
            ['question_id' => 5, 'label' => 'B'],
        ];

        $result = $service->orderRows($rows, Quiz::ORDER_MODE_RANDOM, function (array &$items): void {
            $items = [$items[2], $items[0], $items[1]];
        });

        $this->assertSame([5, 1, 2], $result);
    }

    public function testRandomLabelGroupModeShufflesGroupsOnly(): void
    {
        $service = new QuizQuestionOrderService();

        $rows = [
            ['question_id' => 1, 'label' => 'A'],
            ['question_id' => 2, 'label' => 'A'],
            ['question_id' => 5, 'label' => 'B'],
            ['question_id' => 6, 'label' => 'B'],
            ['question_id' => 7, 'label' => ' C '],
            ['question_id' => 8, 'label' => 'C'],
            ['question_id' => 9, 'label' => null],
            ['question_id' => 10, 'label' => ''],
        ];

        $result = $service->orderRows($rows, Quiz::ORDER_MODE_RANDOM_LABEL_GROUPS, function (array &$items): void {
            $items = [$items[1], $items[3], $items[0], $items[2]];
        });

        $this->assertSame([5, 6, 9, 10, 1, 2, 7, 8], $result);
    }
}
