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
        .question p {
            white-space: pre-wrap; /* Preserve spaces and line breaks */
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
        p {
            margin: 20px;
        }
        .quiz-button {
            font-size: 10px;
            padding: 2px 5px;
            min-width: 55px;
        }
    </style>
</head>
<body>
    <h1><?= $quiz['name']; ?></h1>

    <?php $index=1; foreach ($questions as $question): ?>
        <p style="color: darkblue;font-weight: bold;"><?="Question ".($index++)?></p>
        <div class="question-container" style="width:60%">
            <div class="question">
                <p><?= $question['id']; ?></p>
                <p><?= $question['question']; ?></p>
            </div>
            <hr>
            <form class="answers">
                <label>
                    a) <input type="checkbox" name="answera" value="a1"> <?= $question['a1']; ?>
                </label>
                <label>
                    b) <input type="checkbox" name="answerb" value="a2"> <?= $question['a2']; ?>
                </label>
                <?php if (!empty($question['a3'])): ?>
                    <label>
                        c) <input type="checkbox" name="answerc" value="a3"> <?= $question['a3']; ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($question['a4'])): ?>
                    <label>
                        d) <input type="checkbox" name="answerd" value="a4"> <?= $question['a4']; ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($question['a5'])): ?>
                    <label>
                        e) <input type="checkbox" name="answere" value="a5"> <?= $question['a5']; ?>
                    </label>
                <?php endif; ?>
                <?php if (!empty($question['a6'])): ?>
                    <label>
                        f) <input type="checkbox" name="answerf" value="a6"> <?= $question['a6']; ?>
                    </label>
                <?php endif; ?>
            </form>
            <?php
                $url = Yii::$app->urlManager->createUrl(['/question/update', 'id' => $question['id'] ]);
                $b1 = Html::a('Edit', $url, [ 'title' => 'Edit',
                    'class' => 'btn btn-outline-primary quiz-button',
                    ]);
            ?>
           <div style="display: flex; justify-content: flex-end; align-items: left;"><?=$b1?></div>
        </div>
    <?php endforeach; ?>
</body>
</html>
