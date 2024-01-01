<?php

use yii\helpers\Html;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Multiple Choice Quiz</title>
    <style>
        /* Add your CSS styles here for formatting the quiz */
        .question-container {
            background-color: #f5f5f5; /* Paper-like background color */
            border: 1px solid #ccc; /* Border to resemble paper */
            padding: 20px; /* Padding to create space inside the paper */
            box-shadow: 3px 3px 5px #888888; /* Slight shadow on the bottom and right sides */
            margin-bottom: 40px; /* Increase the margin for more space between questions */
            font-family: monospace; /* Use a non-proportional font (e.g., monospace) */
        }
        .question {
            white-space: pre-wrap; /* Preserve spaces and line breaks */
            margin-left: 20px;
        }
        .answers {
            margin-left: 20px; /* Adjust the margin for answers */
        }
        .answers label {
            display: block; /* Put each answer on a new line */
        }
        label{
            margin: 20px;
        }
        hr {
            margin: 40px;
        }
        pre {
            margin-left: 40px;
            font-size: 16px;
            font-family: monospace;
            color: darkblue;
        }
        .quiz-button {
            font-size: 10px;
            padding: 2px 5px;
            margin-left: 10px;
            min-width: 55px;
        }
    </style>
    <script>
        function highlightCheckbox(questionId, answerNo) {
            var checkboxId = "answer-" + questionId + "-" + answerNo;
            var checkbox = document.getElementById(checkboxId);
            var answerButton = document.getElementById('answer-button'+questionId);
            var backgroundColor = checkbox.style.backgroundColor;

            if (checkbox) {
                if ( backgroundColor && backgroundColor !== 'none') {
                    checkbox.removeAttribute('style');
                    answerButton.textContent = 'Answer';
                } else {
                    answerButton.textContent = 'Hide';
                    checkbox.style.border = "2px solid green";
                    checkbox.style.backgroundColor = "lightgreen";
                }
                
            } else {
                console.log("Checkbox ("+checkboxId+") not found for questionId: " + questionId);
            }
        }
    </script>
</head>
<body>
    <h1><?= $quiz['name']; ?></h1>

    <?php $index=1; foreach ($questions as $question): ?>
        <p style="color: darkblue;font-weight: bold;"><?="Question ".($index++)?></p>
        <div class="question-container" style="width:60%">
            <div class="question">
<?= $question['question']; ?>
            </div>
            <hr>
            <form class="answers">
                <label id="answer-<?=$question['id']?>-1">
                    a) <input type="checkbox" name="answer1" value="a1"> <?= $question['a1']; ?>
                </label>
                <label id="answer-<?=$question['id']?>-2">
                    b) <input type="checkbox" name="answer2" value="a2"> <?= $question['a2']; ?>
                </label>
                <?php if (!empty($question['a3'])): ?>
                    <label id="answer-<?=$question['id']?>-3">
                    c) <input type="checkbox" name="answer3" value="a3"> <?= $question['a3']; ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($question['a4'])): ?>
                    <label id="answer-<?=$question['id']?>-4">
                    d) <input type="checkbox" name="answer4" value="a4"> <?= $question['a4']; ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($question['a5'])): ?>
                    <label id="answer-<?=$question['id']?>-5">
                    e) <input type="checkbox" name="answer5" value="a5"> <?= $question['a5']; ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($question['a6'])): ?>
                    <label id="answer-<?=$question['id']?>-6">
                    f) <input type="checkbox" name="answer6" value="a6"> <?= $question['a6']; ?>
                    </label>
                <?php endif; ?>
            </form>
            <?php          
                $url = Yii::$app->urlManager->createUrl(['/question/update', 'id' => $question['id'] ]);
                $b1 = Html::a('Edit', $url, [ 'title' => 'Edit',
                    'class' => 'btn btn-outline-primary quiz-button',
                    ]);
                $b2 = Html::button('Answer', [
                        'id' => "answer-button".$question['id'],
                        'class' => 'btn btn-outline-danger quiz-button',
                        'onclick' => "highlightCheckbox(".$question['id'].",".$question['correct'].")", 
                    ]);
            ?>
           <div style="display: flex; justify-content: flex-end; align-items: left;"><?=$b1?><?=$b2?></div>
        </div>
    <?php endforeach; ?>
</body>
</html>
