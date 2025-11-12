<!DOCTYPE html>
<html>

<?php
require_once Yii::getAlias('@app/views/include/functions.php');
?>

<head>
    <title>Multiple Choice Quiz</title>
    <style>
        .question {
            white-space: pre-wrap;
            margin-left: 20px;
            margin-top:100px;
            font-family: monospace;
            page-break-inside: avoid;
            position: relative;
        }

        @media print {
            .pagebreak {
                page-break-before: always;
            }
        }

        label {
            margin: 20px;
        }

        hr {
            margin: 40px;
        }

        pre {
            margin-top: 20px;
        }

        pre pre {
            margin-left: 30px;
            font-size: 16px;
            color: darkblue;
            border-left: 2px solid lightgray;
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <h1><?= $quiz['name']; ?></h1>

    <?php $index = 0; $index++;
    foreach ($questions as $question) : ?>
        <div class="question>">
            <div style="color: darkblue;font-weight: bold;"><?= "Question " . $question['id'] ?></div>
<?= escapeHtmlExceptTags($question['question']); ?>
            <hr>
            <div class="pagebreak"> </div>
        </div>
    <?php endforeach; ?>
</body>

</html>