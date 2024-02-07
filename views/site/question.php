<?php

use yii\helpers\Html;
use yii\helpers\Url;

$csrfTokenName = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$answers = [];
for ($i = 1; $i < 7; $i++) {
    if (rtrim($question['a' . $i], "\n\r") != "") {
        array_push($answers, 'a' . $i);
    }
}
shuffle($answers);
$noAnswers = count($answers);

// escape HTML tags to dispay properly in browser and 
// don't convert pre and code becasue these are used for formatting.
// function escapeHtmlExceptTags($html, $deleteTags = [], $allowedTags = ['pre', 'code', 'i', 'b'])
require_once Yii::getAlias('@app/views/include/functions.php');


// for proper formatting teh answer, I need to know if long words occur.
function hasLongAnswer($string, $maxLength = 60)
{
    if (strlen($string) > 70)
        return true;

    $words = explode(' ', $string);
    foreach ($words as $word) {
        if (strlen($word) > $maxLength) {
            return true;
        }
    }
    return false;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Question and Answers</title>

    <style>
        .background-image {
            position: relative;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 1) 200px, rgba(255, 255, 255, 0.85) 100%),
                url('<?= Url::to('@web/img/classroom.webp') ?>');
            background-size: cover;
            background-position: center;
            height: 100vh;
            /* Full height of the viewport */
        }

        .answer {
            padding: 15px;
            border: 2px solid #ddd;
            background-color: #fbfbfd;
            margin: 30px;
            cursor: pointer;
            text-align: left;
            min-height: 3em;
            font-family: monospace;
            user-select: none;
            display: flex;
            justify-content: center;
        }

        .long-answer {
            font-size: smaller;
            min-height: 7em;
            margin-left: 0px;
            margin-right: 0px;
            padding-top: 10px;
            padding-bottom: 5px;
        }

        .answer:not(.selected):hover {
            background-color: #bbd7fc;
        }

        .answer:active {
            background-color: #007bff;
        }

        .selected {
            background-color: #007bff;
            color: white;
        }

        .question-block {
            white-space: pre-wrap;
            font-family: monospace;
            background-color: #f8f8f7;
            border: 1px solid #ddd;
            padding-left: 40px;
            padding-bottom: 20px;
            min-height: 6em;
            text-align: left;
            user-select: none;
            overflow-x: hidden;
        }

        .question-title {
            margin-top: 10px;
            margin-bottom: 6px;
            font-size: larger;
            text-align: left;
            color: darkblue;
        }

        .banner-container {
            position: relative;
            background-image: url('<?= Url::to('@web/img/banner1.jpg') ?>');
            background-size: cover;
            background-position: center;
            padding: 16px;
        }


        @media (max-width: 601px) {
            .question-block {
                white-space: normal;
                min-height: 4em;
                font-size: smaller;
            }

            .answer {
                margin: 10px;
                min-height: 3em;
            }

            .question-title {
                margin-top: 20px;
                margin-bottom: 5px;
                font-size: smaller;
            }

            .banner-container {
                padding: 4px;
            }
        }

        .banner-content {
            position: relative;
            z-index: 2;
            color: black;
            text-shadow:
                -1px -1px 0 #fff,
                1px -1px 0 #fff,
                -1px 1px 0 #fff,
                1px 1px 0 #fff;
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

        .footer {
            color: #d0d0d0;
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

        code,
        pre {
            margin-left: 30px;
            font-size: 16px;
            color: darkblue;
            border-left: 2px solid lightgray;
            padding-left: 10px;
        }

        .btn {
            margin-right: 20px;
        }

        .quiz-button {
            font-size: 12px;
            padding: 2px 5px;
            min-width: 55px;
            margin: 5px;
        }

        .alert-error {
            padding: 35px;
            margin: 40px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #333;
            border-color: #e0e0e0;
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Delay the animation to ensure the page has fully loaded
            setTimeout(function () {
                const page = document.querySelector(".page-effect");
                page.style.transform = "translateX(0)"; // Slide the page in from the right
            }, 100);
        });
    </script>

</head>

<body class="background-image">

    <div class="container-fluid banner-container text-white text-center py-3">
        <div class="banner-content">
            <h1>
                <?= $title ?>
            </h1>
            <p>vraag
                <?= $submission['no_answered'] + 1 ?> van
                <?= $submission['no_questions'] ?>
            </p>
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert-error">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="container text-center">
        <div class="row d-flex justify-content-center align-items-start">
            <div class="col-12 question-title">Vraag
                <?= $submission['no_answered'] + 1 ?>
            </div>

            <div class="question-block">
                <!-- this code needs to be non-idented becasue pre is used for formatting -->
<?php if (isset($quiz['blind']) && $quiz['blind']) { // view is also called from backend when adding a question in which case $quiz is not provided....
echo "On paper, look up question with id: <b>" . $question['id'] . "</b><br><br>Then, select the right answer....";
} else {
echo escapeHtmlExceptTags($question['question']);
} ?>
                <!-- end of non-identation-->
            </div>


            <?php
            // check if there are long answers and if so add a style
            $style = "";
            for ($i = 0; $i <= 5; $i++) {
                if ($noAnswers > $i && hasLongAnswer($question[$answers[($i)]])) {
                    $style = "long-answer";
                }
            }
            ?>

            <div class="col-md-6">
                <!-- Answers Column 1, 3, 5 -->
                <?php for ($i = 1; $i <= 5; $i += 2) { ?>
                    <?php if ($noAnswers >= $i) { ?>
                        <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[($i - 1)] ?>')">
                            <?= escapeHtmlExceptTags( $question[$answers[$i - 1]] , ['pre']) ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

            <div class="col-md-6">
                <!-- Answers Column 2, 4, 6 -->
                <?php for ($i = 2; $i <= 6; $i += 2) { ?>
                    <?php if ($noAnswers >= $i) { ?>
                        <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[($i - 1)] ?>')">
                        <?= escapeHtmlExceptTags( $question[$answers[$i - 1]] , ['pre']) ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>


            <form id="answer" class="mt-4" action="<?= Url::to(['site/answer']) ?>" method="POST">
                <input type="hidden" id="selectedAnswer" name="selectedAnswer">
                <input type="hidden" id="no_answered" name="no_answered" value="<?= $submission['no_answered']; ?>">
                <input type="hidden" name="<?= $csrfTokenName ?>" value="<?= $csrfToken ?>">
                <?php if ($submission['id'] != 0) { ?>
                    <button type="button" id="submitButton" class="btn btn-light" style="margin-bottom:10px;" title="Click eerst op een antwoord"
                        disabled>Volgende vraag >></button>
                <?php } else {
                    $url = Yii::$app->urlManager->createUrl(['/question/update', 'id' => $question['id']]);
                    echo Html::a('Edit', $url, [
                        'id' => 'submitButton-org1',
                        'title' => 'Edit Question',
                        'class' => 'btn btn-outline-secondary quiz-button',
                    ]);
                    $url = Yii::$app->urlManager->createUrl(['/question/copy', 'id' => $question['id']]);
                    echo Html::a('Copy', $url, [
                        'id' => 'submitButton-org2',
                        'title' => 'Copy Question',
                        'class' => 'btn btn-outline-secondary quiz-button',
                    ]);
                    $returnUrl = Yii::$app->user->returnUrl ?: ['/quiz'];
                    echo Html::a('Back', $returnUrl, [
                        'id' => 'submitButton-org2',
                        'title' => 'Back',
                        'class' => 'btn btn-outline-secondary quiz-button',
                    ]);
                } ?>
            </form>
        </div>

        <div class="banner-content footer">
            <h1>
                <br>
                <?= $title ?>
            </h1>
        </div>

        <script>
            function selectAnswer(element, answer) {
                // Remove 'selected' class from all answers
                document.querySelectorAll('.answer').forEach(function (el) {
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
            document.getElementById('submitButton').addEventListener('click', function () {
                this.disabled = true;
                this.innerText = 'Submitting...';
                document.getElementById('answer').submit();
            });
        </script>

    </div>

</body>

</html>