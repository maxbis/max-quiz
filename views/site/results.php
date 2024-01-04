<!DOCTYPE html>
<html>

<head>
    <title>Results</title>

    <style>
        .question-container {
            margin-left: 20px;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 3px 3px 5px #888888;
            margin-bottom: 40px;
            font-family: monospace;
            width: 60%;
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

        @media (max-width: 800px) {
            .question-container {
                width: 90%;
                font-size: smaller;
            }
        }
    </style>
</head>

<body>
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
        <p style="color: darkblue;font-weight: bold;"><?= "Question " . ($i + 1) ?></p>
    <?php
        echo "<div class=\"question-container\" >";
        if (array_key_exists($thisQuestionId, $questionsById)) {
            echo "<p>" . $questionsById[$thisQuestionId]['question'] . "</p>";
            echo "<br>";
            // echo $answerArray[$i];
            // echo $questionsById[$thisQuestionId]['correct'];

            if (isset($answerArray[$i])) {
                if ($answerArray[$i] == $questionsById[$thisQuestionId]['correct']) {
                    echo "<p class=\"correct\">Correct answer given: ";
                    echo $questionsById[$thisQuestionId]['a' . $answerArray[$i]];
                    echo "</p>";
                } else {
                    echo "<p class=\"incorrect\">Incorrect answer given: ";
                    echo $questionsById[$thisQuestionId]['a' . $answerArray[$i]];
                    echo "</p>";
                }
            } else {
                echo "<p>Question not answered</p>";
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

    <p style="color: darkblue;font-weight: bold;">Summary for <?= $submission['first_name'] . " " . $submission['last_name'] ?></p>
    <div class="question-container" style="width:60%">
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
</body>