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
}
