<?php

use yii\helpers\Html;
use yii\helpers\Url;

$csrfTokenName = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$answers = [];
for ($i = 1; $i < 7; $i++) {
    if ($question['a' . $i] != "") {
        array_push($answers, 'a' . $i);
    }
}
shuffle($answers);
$noAnswers = count($answers);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question and Answers</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .background-image {
            position: relative;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 1) 80%, rgba(255, 255, 255, 0.75) 100%),
                url('<?= Url::to('@web/img/classroom.webp') ?>');
            background-size: cover;
            background-position: center;
            height: 100vh;
            /* Full height of the viewport */
        }

        .answer {
            padding: 6px;
            border: 1px solid #ddd;
            margin: 40px;
            cursor: pointer;
            text-align: left;
            min-height: 4em;
            font-family: monospace;
            user-select: none;
        }

        .selected {
            background-color: #007bff;
            color: white;
        }

        .question-block {
            font-family: monospace;
            /* Monospaced font */
            background-color: #f8f8f8;
            /* Paper-like background color */
            border: 1px solid #ddd;
            /* Optional: adds a subtle border */
            padding: 15px;
            /* Padding around the text */
            min-height: 9em;
            /* Minimum height for about five lines of text */
            text-align: left;
            /* Align text to the left */
            user-select: none;
        }

        .question-title {
            margin-top: 80px;
            font-size: larger;
            /* Makes the font larger */
            text-align: left;
            /* Aligns text to the left */
        }

        .banner-content {
            position: relative;
            z-index: 2;
            /* Ensures the content is above the pseudo-element */
            color: black;
            text-shadow:
                -1px -1px 0 #fff,
                1px -1px 0 #fff,
                -1px 1px 0 #fff,
                1px 1px 0 #fff;
        }

        .banner-container {
            position: relative;
            background-image: url('<?= Url::to('@web/img/banner1.jpg') ?>');
            background-size: cover;
            background-position: center;
        }

        .banner-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            /* White with 60% opacity */
            z-index: 1;
        }

        .page-effect {
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            /* Set your background color */
            transform: translateX(100%);
            /* Initially slide the page out of view */
            transition: transform 0.5s ease-in-out;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Delay the animation to ensure the page has fully loaded
            setTimeout(function() {
                const page = document.querySelector(".page-effect");
                page.style.transform = "translateX(0)"; // Slide the page in from the right
            }, 100);
        });
    </script>
</head>

<body class="background-image">

    <div class="container-fluid banner-container text-white text-center py-3">
        <div class="banner-content">
            <h1><?= $title ?></h1>
            <p>vraag <?= $submission['no_answered'] + 1 ?> van <?= $submission['no_questions'] ?></p>
        </div>
    </div>

    <div class="container text-center">
        <div class="row justify-content-center page-effect">
            <div class="col-12 question-title">Vraag <?= $submission['no_answered'] + 1 ?></div>
            <div class="col-12">
                <div class="my-4 question-block">
                    <pre><?= $question['question'] ?></pre>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Answers Column 1 -->
                <?php if ($noAnswers >= 1) { ?>
                    <div class="answer" onclick="selectAnswer(this, '<?= $answers[0] ?>')"><?= $question[$answers[0]] ?></div>
                <?php } ?>
                <?php if ($noAnswers >= 3) { ?>
                    <div class="answer" onclick="selectAnswer(this, '<?= $answers[2] ?>')"><?= $question[$answers[2]] ?></div>
                <?php } ?>
                <?php if ($noAnswers >= 5) { ?>
                    <div class="answer" onclick="selectAnswer(this, '<?= $answers[4] ?>')"><?= $question[$answers[4]] ?></div>
                <?php } ?>
            </div>

            <div class="col-md-6">
                <!-- Answers Column 2 -->
                <?php if ($noAnswers >= 2) { ?>
                    <div class="answer" onclick="selectAnswer(this, '<?= $answers[1] ?>')"><?= $question[$answers[1]] ?></div>
                <?php } ?>
                <?php if ($noAnswers >= 4) { ?>
                    <div class="answer" onclick="selectAnswer(this, '<?= $answers[3] ?>')"><?= $question[$answers[3]] ?></div>
                <?php } ?>
                <?php if ($noAnswers >= 6) { ?>
                    <div class="answer" onclick="selectAnswer(this, '<?= $answers[5] ?>')"><?= $question[$answers[5]] ?></div>
                <?php } ?>
            </div>
            <div class="col-md-6">
                <div class="col-12">
                    <form id="answer" class="mt-4" action="<?= Url::to(['site/answer']) ?>" method="POST">
                        <input type="hidden" id="selectedAnswer" name="selectedAnswer">
                        <input type="hidden" name="<?= $csrfTokenName ?>" value="<?= $csrfToken ?>">
                        <button type="submit" id="submitButton" class="btn btn-light" title="Click eerst op een antwoord" disabled>Volgende vraag >></button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function selectAnswer(element, answer) {
                // Remove 'selected' class from all answers
                document.querySelectorAll('.answer').forEach(function(el) {
                    el.classList.remove('selected');
                });

                // Add 'selected' class to clicked answer
                element.classList.add('selected');

                // Set the value of the hidden input
                document.getElementById('selectedAnswer').value = answer.substring(1);

                // Enable submit button
                document.getElementById('submitButton').className = "btn btn-danger";
                document.getElementById('submitButton').title = "Click voor volgende vraag";
                document.getElementById('submitButton').disabled = false;
            }
        </script>

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>