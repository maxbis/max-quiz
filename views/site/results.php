<?php

use yii\helpers\Html;

require_once Yii::getAlias('@app/views/include/functions.php');

?>

<!DOCTYPE html>
<html>

<head>
    <title>Results</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
        .main {
            margin-top: 40px;
        }

        .question-container {
            margin-left: 20px;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 3px 3px 5px #888888;
            margin-bottom: 40px;
            font-family: monospace;
            max-width: 1000px;
        }

        pre {
            margin-left: 30px;
            font-size: 16px;
            color: darkblue;
            border-left: 2px solid lightgray;
            padding-left: 10px;
        }

        .correct {
            background-color: lightgreen;
        }

        .incorrect {
            background-color: salmon;
        }

        @media (max-width: 801px) {
            .question-container {
                width: 90%;
            }

            .main {
                margin-top: 20px;
            }
        }

        .myButton {
            display: inline-block;
            outline: none;
            cursor: pointer;
            padding: 0 16px;
            background-color: #fff;
            border-radius: 0.25rem;
            border: 1px solid #dddbda;
            color: #0070d2;
            font-size: 13px;
            line-height: 30px;
            font-weight: 400;
            text-align: center;
            text-decoration: none;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }

        .myButton:hover {
            background-color: lightblue;
            color: black;
        }

        .centered {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 120px;
            margin-bottom: 40px;
            max-width: 600px;
        }
    </style>

</head>

<body>
    <div class="main container">
        <h1>Results for <?= $submission['first_name'] . " " . $submission['last_name'] ?></h1>
        <hr style="width:60%">
        <?php
        $score = round($submission['no_correct'] * 100 / $submission['no_questions'], 0);
        $questionIdArray = explode(" ", ltrim($submission['question_order']));
        $answerArray = explode(" ", ltrim($submission['answer_order']));
        // _d($submission['question_order']);
        // _d($submission['answer_order']);
        // _d($answerArray);
        
        $i = 0;
        foreach ($questionIdArray as $thisQuestionId) {
            ?>
            <p style="color: darkblue;font-weight: bold;">
                <?= "Question " . ($i + 1) ?>
                <?php
                    // if logged in show how many % of users in the current course scored on this question; this will not be shown to a student!
                    if ( !Yii::$app->user->isGuest ) {
                        echo "<span style=\"color:#d0d0d0; font-weight: normal;\"> (id: " . $thisQuestionId . ", correct: " . ($stats[$thisQuestionId] ?? "-") . "%)
                        </span>";
                    }
                ?>
            </p>
            <?php
            echo "<div class=\"question-container\" >";
            if (array_key_exists($thisQuestionId, $questionsById)) {
                echo "<p>" . escapeHtmlExceptTags($questionsById[$thisQuestionId]['question']) . "</p><br>";
                // echo $answerArray[$i];
                // echo $questionsById[$thisQuestionId]['correct'];
                if (isset($answerArray[$i]) && $answerArray[$i]) {
                    if ($answerArray[$i] == $questionsById[$thisQuestionId]['correct']) {
                        echo "<p class=\"correct\">Correct answer given: ";
                        echo htmlspecialchars($questionsById[$thisQuestionId]['a' . $answerArray[$i]]);
                        echo "</p>";
                    } else {
                        echo "<p class=\"incorrect\">Incorrect answer given: ";
                        echo htmlspecialchars($questionsById[$thisQuestionId]['a' . $answerArray[$i]]);
                        echo "</p>";
                    }
                } else {
                    echo "<p>Question not yet answered</p>";
                }
            } else {
                echo "Question does not exists anymore...";
                echo "<br>";
            }
            echo "</div>";
            echo " <hr style=\"width:60%\">";
            $i++;
        }
        ?>

        <p style="color: darkblue;font-weight: bold;">Summary for
            <?= $submission['first_name'] . " " . $submission['last_name'] ?></p>
        <div class="question-container">
            <table>
                <tr>
                    <td>
                        Number of questions
                    </td>
                    <td>
                        <?= $submission['no_questions'] ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Number of correct answers&nbsp;
                    </td>
                    <td>
                        <?= $submission['no_correct'] ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Score
                    </td>
                    <td>
                        <b><?= $score ?></b>%
                    </td>
                </tr>
            </table>
        </div>

        <div class="centered">
            <?= Html::a('Clear', ['/submission/create', 'token' => $submission['token']], ['class' => 'myButton', 'title' => 'Start new quiz']); ?>
        </div>
    </div>
</body>