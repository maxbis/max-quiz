<?php

namespace tests\unit\models;

use app\models\Quiz;

class QuizTest extends \Codeception\Test\Unit
{
    public function testDerivesQuizGroupFromNamePrefix()
    {
        verify(Quiz::deriveQuizGroup('math-fractions.en'))->equals('math-fractions');
    }

    public function testDerivesQuizGroupFromWholeNameWhenNoDotExists()
    {
        verify(Quiz::deriveQuizGroup('General Knowledge'))->equals('General Knowledge');
    }

    public function testBuildAggregationKeyUsesGroupAndTrimmedName()
    {
        verify(Quiz::buildAggregationKey('C25-B7', ' Quiz A ', 10))->equals('C25-B7||Quiz A');
    }

    public function testBuildAggregationKeyFallsBackToQuizIdWhenGroupIsEmpty()
    {
        verify(Quiz::buildAggregationKey('  ', 'Quiz A', 10))->equals('quiz:10');
    }
}
