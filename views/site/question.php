<?php

use yii\helpers\Html;
use yii\helpers\Url;

$csrfTokenName = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$answers = [];
for ($i = 1; $i < 7; $i++) {
    if (rtrim((string) ($question['a' . $i] ?? ''), "\n\r") !== "") {
        array_push($answers, 'a' . $i);
    }
}
shuffle($answers);
$noAnswers = count($answers);

// escape HTML tags to dispay properly in browser and 
// don't convert pre and code becasue these are used for formatting.
// function escapeHtmlExceptTags($html, $deleteTags = [], $allowedTags = ['pre', 'code', 'i', 'b'])
require_once Yii::getAlias('@app/views/include/functions.php');


// for proper formatting the answer, I need to know if long words occur.
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

$selectedRecords = Yii::$app->session->get('selectedQuestionIds', []);
// $returnUrl is now passed from the controller, but fallback to session if not provided
if (!isset($returnUrl) || empty($returnUrl)) {
    $returnUrl = Yii::$app->session->get('viewReturnUrl', '');
}

if ($selectedRecords == null) {
    $prevRecordId = null;
    $nextRecordId = null;
    $vraagNr = [1, 0];
} else {
    $currentRecordId = $question['id'];
    $currentPosition = array_search($currentRecordId, $selectedRecords);
    $prevRecordId = $currentPosition > 0 ? $selectedRecords[$currentPosition - 1] : null;
    $nextRecordId = $currentPosition < count($selectedRecords) - 1 ? $selectedRecords[$currentPosition + 1] : null;
    $vraagNr = [count($selectedRecords), $currentPosition];
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
        body {
            font-family: 'Consolas', 'Menlo', 'Liberation Mono', 'Courier New', monospace;
            color: black;
        }

        .background-image {
            position: relative;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 1) 200px, rgba(255, 255, 255, 0.85) 80%, rgba(255, 255, 255, 1) 100%),
                url('<?= Url::to('@web/img/classroom.webp') ?>');
            background-size: cover;
            background-position: center;
            height: 100%;
            /* Full height of the viewport */
        }

        .answer {
            padding: 20px 25px;
            border: 2px solid #e0e0e0;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            margin: 20px;
            cursor: pointer;
            text-align: left;
            min-height: 3em;
            font-family: monospace;
            user-select: none;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .answer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.1) 0%, rgba(0, 86, 179, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .long-answer {
            font-size: smaller;
            min-height: 7em;
            margin-left: 10px;
            margin-right: 10px;
            padding-top: 15px;
            padding-bottom: 10px;
        }

        .answer:not(.selected):hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-color: #2196F3;
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.25);
            transform: translateY(-3px);
        }

        .answer:not(.selected):hover::before {
            opacity: 1;
        }

        .answer:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }

        .selected {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border-color: #4CAF50;
            box-shadow: 0 8px 24px rgba(76, 175, 80, 0.35);
            transform: translateY(-3px) scale(1.02);
            animation: selectPulse 0.4s ease-out;
        }

        .selected::after {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: white;
            animation: checkmarkAppear 0.3s ease-out;
        }

        @keyframes selectPulse {
            0% {
                transform: translateY(-3px) scale(1);
            }
            50% {
                transform: translateY(-3px) scale(1.05);
            }
            100% {
                transform: translateY(-3px) scale(1.02);
            }
        }

        @keyframes checkmarkAppear {
            0% {
                opacity: 0;
                transform: scale(0) rotate(-45deg);
            }
            50% {
                transform: scale(1.2) rotate(0deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
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
                padding: 15px 18px;
                border-radius: 10px;
            }

            .answer:not(.selected):hover {
                transform: translateY(-2px);
            }

            .selected {
                transform: translateY(-2px) scale(1.01);
            }

            .selected::after {
                font-size: 20px;
                top: 8px;
                right: 12px;
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
                <?php
                if ($submission['id'] != 0) {
                    echo $submission['no_answered'] + 1;
                    echo " van ";
                    echo $submission['no_questions'];
                } else {
                    echo $vraagNr[0] - $vraagNr[1];
                    echo " van ";
                    echo $vraagNr[0];

                }
                ?>
            </p>
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert-error">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="container text-center">
        <div class="row d-flex justify-content-center align-items-start  page-effect">
            <div class="col-12 question-title">Vraag
                <?php
                if ($submission['id'] != 0) {
                    echo $submission['no_answered'] + 1;
                } else {
                    echo $vraagNr[0] - $vraagNr[1];
                }
                ?>
            </div>

            <div class="question-block">

            <!-- this code needs to be non-idented becasue pre is used for formatting -->

<?php if (isset($quiz['blind']) && $quiz['blind']) { // view is also called from backend when adding a question in which case $quiz is not provided....
echo "On paper, look up question with id: <b>" . $question['id'] . "</b><br><br>Then, select the right answer....";
} else {
echo escapeHtmlExceptTags($question['question']);
}
?>
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

            <!-- Row 1: Answers 1 and 2 -->
            <?php if ($noAnswers >= 1) { ?>
                <div class="col-md-6">
                    <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[0] ?>')">
                        <?= escapeHtmlExceptTags($question[$answers[0]], ['pre']) ?>
                    </div>
                </div>
            <?php } ?>
            <?php if ($noAnswers >= 2) { ?>
                <div class="col-md-6">
                    <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[1] ?>')">
                        <?= escapeHtmlExceptTags($question[$answers[1]], ['pre']) ?>
                    </div>
                </div>
            <?php } ?>

            <!-- Row 2: Answers 3 and 4 -->
            <?php if ($noAnswers >= 3) { ?>
                <div class="col-md-6">
                    <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[2] ?>')">
                        <?= escapeHtmlExceptTags($question[$answers[2]], ['pre']) ?>
                    </div>
                </div>
            <?php } ?>
            <?php if ($noAnswers >= 4) { ?>
                <div class="col-md-6">
                    <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[3] ?>')">
                        <?= escapeHtmlExceptTags($question[$answers[3]], ['pre']) ?>
                    </div>
                </div>
            <?php } ?>

            <!-- Row 3: Answers 5 and 6 -->
            <?php if ($noAnswers >= 5) { ?>
                <div class="col-md-6">
                    <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[4] ?>')">
                        <?= escapeHtmlExceptTags($question[$answers[4]], ['pre']) ?>
                    </div>
                </div>
            <?php } ?>
            <?php if ($noAnswers >= 6) { ?>
                <div class="col-md-6">
                    <div class="answer <?= $style ?>" onclick="selectAnswer(this, '<?= $answers[5] ?>')">
                        <?= escapeHtmlExceptTags($question[$answers[5]], ['pre']) ?>
                    </div>
                </div>
            <?php } ?>


            <form id="answer" class="mt-4" action="<?= Url::to(['site/answer']) ?>" method="POST">
                <input type="hidden" id="selectedAnswer" name="selectedAnswer">
                <input type="hidden" id="no_answered" name="no_answered" value="<?= $submission['no_answered']; ?>">
                <input type="hidden" name="<?= $csrfTokenName ?>" value="<?= $csrfToken ?>">
                <?php if ($submission['id'] != 0) { ?>
                    <button type="button" id="submitButton" class="btn btn-light" style="margin-bottom:10px;"
                        title="Click eerst op een antwoord" disabled>Volgende vraag >></button>
                <?php } else {
                    // Check if quiz_id is provided (coming from edit-labels page)
                    if (isset($quiz_id) && $quiz_id !== null) {
                        // Show only Edit and Back buttons when coming from edit-labels
                        $url = Yii::$app->urlManager->createUrl(['/question/update', 'id' => $question['id']]);
                        echo Html::a('Edit', $url, [
                            'id' => 'submitButton-edit',
                            'title' => 'Edit Question',
                            'class' => 'btn btn-outline-secondary quiz-button',
                        ]);
                        echo Html::a('Back', $returnUrl, [
                            'id' => 'submitButton-back',
                            'title' => 'Back to Edit Labels',
                            'class' => 'btn btn-outline-secondary quiz-button',
                        ]);
                    } else {
                        // Show all buttons for normal view
                        if ($nextRecordId !== null) {
                            $url = Yii::$app->urlManager->createUrl(['/question/view', 'id' => $nextRecordId]);
                            echo Html::a('<<', $url, [
                                'id' => 'submitButton-org1',
                                'title' => 'Prev Question',
                                'class' => 'btn btn-outline-secondary quiz-button',
                            ]);
                        }
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
                        $url = Yii::$app->urlManager->createUrl(['/question/alternative', 'question_id' => $question['id']]);
                        echo Html::a('Alternative', $url, [
                            'id' => 'submitButton-org2',
                            'title' => 'Create alternative question',
                            'class' => 'btn btn-outline-secondary quiz-button',
                        ]);
                        echo Html::a('Back', $returnUrl, [
                            'id' => 'submitButton-org2',
                            'title' => 'Back',
                            'class' => 'btn btn-outline-secondary quiz-button',
                        ]);
                        if ($prevRecordId !== null) {
                            $url = Yii::$app->urlManager->createUrl(['/question/view', 'id' => $prevRecordId]);
                            echo Html::a('>>', $url, [
                                'id' => 'submitButton-org1',
                                'title' => 'Next Question',
                                'class' => 'btn btn-outline-secondary quiz-button',
                            ]);
                        }
                    }
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
            let initialX, initialY;
            let dragging = false;

            document.addEventListener('mousedown', function (event) {
                // Store the initial mouse position when the mouse button is pressed
                initialX = event.clientX;
                initialY = event.clientY;
                dragging = true; // Set dragging to true on mousedown
            });

            document.addEventListener('mouseup', function () {
                dragging = false; // Reset dragging on mouseup
            });

            document.addEventListener('mousemove', function (event) {
                if (dragging) {
                    // Calculate the distance moved
                    let deltaX = Math.abs(event.clientX - initialX);
                    let deltaY = Math.abs(event.clientY - initialY);

                    if ((deltaX + deltaY) > 50 ) {
                        // Clear any text selection to prevent cheating
                        if (window.getSelection) {
                            window.getSelection().removeAllRanges();
                        } else if (document.selection) {
                            document.selection.empty();
                        }
                        
                        document.body.style.visibility = 'hidden'; // Blank the page
                        setTimeout(function () {
                            document.body.style.visibility = 'visible'; 
                        }, 2000); 

                        // Reset dragging to prevent repeated triggering
                        dragging = false;
                    }
                }
            });


            function selectAnswer(element, answer) {
                // Remove 'selected' class from all answers with fade-out effect
                document.querySelectorAll('.answer').forEach(function (el) {
                    if (el.classList.contains('selected')) {
                        // Add a subtle fade-out animation
                        el.style.transition = 'all 0.2s ease-out';
                    }
                    el.classList.remove('selected');
                });

                // Small delay before adding selected class for smoother transition
                setTimeout(function() {
                    // Add 'selected' class to clicked answer
                    element.classList.add('selected');
                }, 50);

                // Set the value of the hidden input
                document.getElementById('selectedAnswer').value = answer.substring(1);

                // Enable submit button with smooth transition
                const submitBtn = document.getElementById('submitButton');
                submitBtn.style.transition = 'all 0.3s ease';
                submitBtn.className = "btn btn-success";
                submitBtn.title = "Click voor volgende vraag";
                submitBtn.disabled = false;
                
                // Add a subtle pulse to the submit button
                submitBtn.style.animation = 'buttonPulse 0.5s ease';
                setTimeout(function() {
                    submitBtn.style.animation = '';
                }, 500);
            }

            // Add button pulse animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes buttonPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                }
            `;
            document.head.appendChild(style);
            document.getElementById('submitButton').addEventListener('click', function () {
                this.disabled = true;
                this.innerText = 'Submitting...';
                document.getElementById('answer').submit();
            });
        </script>

    </div>

</body>

</html>