<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

function getStats($data)
{
    $answer_no = isset($data['answer']) ? $data['answer'] : 0;
    $answer_correct = isset($data['correct']) ? $data['correct'] : 0;

    if ($answer_no != 0) {
        $perc_correct = round($answer_correct / $answer_no * 100);
        $stats = "$answer_no / $answer_correct ($perc_correct%)";
    } else {
        $perc_correct = 0;
        $stats = "-";
    }
    return ($stats);
}

require_once Yii::getAlias('@app/views/include/functions.php');

?>
<!DOCTYPE html>
<html>

<head>
    <title>Multiple Choice Quiz</title>
    <style>
        .question-container {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 3px 3px 5px #888888;
            margin-bottom: 40px;
            font-family: monospace;
            font-size:24px;
        }
        .question-container pre {
            font-size: 26px;
        }

        .question {
            white-space: pre-wrap;
        }

        .answers {
            margin-left: 20px;
        }

        .answers label {
            display: block;
        }

        label {
            margin: 20px;
            min-width: 180px;
        }

        hr {
            margin: 40px;
        }

        pre {
            margin-left: 30px;
            font-size: 16px;
            color: darkblue;
            border-left: 2px solid lightgray;
            padding-left: 10px;
        }

        .quiz-button {
            font-size: 10px;
            padding: 2px 5px;
            margin-left: 10px;
            min-width: 55px;
        }

        .stats {
            color: darkblue;
            display: none;
        }

        @keyframes breathe {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(2);
            }
        }

        .breathing {
            animation: breathe 1s ease-in-out infinite;
        }
    </style>
    <script>
        function highlightCheckbox(questionId, answerNo) {
            var checkboxId = "answer-" + questionId + "-" + answerNo;
            var checkbox = document.getElementById(checkboxId);
            var answerButton = document.getElementById('answer-button' + questionId);
            var backgroundColor = checkbox.style.backgroundColor;
            var stats = document.getElementById("stats" + questionId);

            if (checkbox) {
                if (backgroundColor && backgroundColor !== 'none') {
                    checkbox.removeAttribute('style');
                    answerButton.textContent = 'Answer';
                    stats.style.display = 'none';
                } else {
                    answerButton.textContent = 'Hide';
                    checkbox.style.backgroundColor = "lightgreen";
                    stats.style.display = 'block';
                }

            } else {
                console.log("Checkbox (" + checkboxId + ") not found for questionId: " + questionId);
            }
        }

        function editQuestion(url) {
            var iframeElement = document.createElement("iframe");

            // Set the source URL for the iframe
            iframeElement.src = url;

            // Set attributes for the iframe (optional)
            iframeElement.width = "500"; // Set the width
            iframeElement.height = "300"; // Set the height
            iframeElement.frameBorder = "0"; // Remove border

            // Append the iframe to the container
            iframeContainer.appendChild(iframeElement);
        }

        document.addEventListener('DOMContentLoaded', function () {
            window.scrollBy(0, -120);

            var hash = window.location.hash; // Get the anchor from the URL
            if (hash) {
                var targetDiv = document.querySelector(hash);
                if (targetDiv) {
                    targetDiv.classList.add('breathing');
                    targetDiv.innerHTML = targetDiv.innerHTML + 'last edited';
                    setTimeout(() => {
                        targetDiv.classList.remove('breathing');
                    }, 1000);
                }
            }
        });
    </script>

</head>

<body>
    <h1>
        <?= $quiz['name']; ?>
    </h1>


    <?php
    // escape HTML tags to dispay properly in browser and 
    // don't convert pre and code becasue these are used for formatting.
    // function escapeHtmlExceptTags($html, $deleteTags = [], $allowedTags = ['pre', 'code', 'i', 'b'])
    require_once Yii::getAlias('@app/views/include/functions.php');

    $index = 1;
    foreach ($questions as $question): ?>
        <div style="display:flex;margin-bottom:5px;">
            <div id="<?= 'q' . $question['id'] ?>" style="color: darkblue;font-weight: bold;">
                <?= "Question " . ($index++) ?>
            </div>
            <div id="stats<?= $question['id'] ?>" class="stats" style="">
                <?php if (isset($logItems[$question['id']])) {
                    echo ",&nbsp;" . getStats($logItems[$question['id']]);
                } else {
                    echo "-";
                } ?>
            </div>
        </div>

        <div class="question-container row">
            <div class="_col">
                <form class="answers">
                    <div class="question" id="question<?= $question['id'] ?>">
<?= escapeHtmlExceptTags($question['question'] ); ?>
                    </div>
            </div>

            <div class="_col" style="border-top:1px dashed gray;margin-top:80px;">

                <?php
                $array = ['1','2','3','4','5','6'];
                shuffle($array);
                $questionLabel = 'a';
                foreach($array as $item) {
                    if ( $question['a'.$item]==='' || $question['a'.$item] === null ) continue;
                    // echo escapeHtmlExceptTags( $question['a'.$item] , ['pre']);
                    ?>
                        <label id="answer-<?= $question['id'].'-'.$item ?>">
                            <?= $questionLabel ?> ) <input type="checkbox" name="answer<?= $item ?>" value="a<?= $item ?>">
                            <?= escapeHtmlExceptTags( $question['a'.$item] , ['pre']) ?>
                        </label>
                        <br>
                    <?php
                    $questionLabel++;
                }
                ?>
                </form>
            </div>

            <?php
            $url = Yii::$app->urlManager->createUrl(['/question/update', 'id' => $question['id']]);
            $b1 = Html::a('Edit', $url, [
                'title' => 'Edit',
                'class' => 'btn btn-outline-primary quiz-button',
            ]);
            $b2 = Html::button('Answer', [
                'id' => "answer-button" . $question['id'],
                'class' => 'btn btn-outline-danger quiz-button',
                'onclick' => "highlightCheckbox(" . $question['id'] . "," . $question['correct'] . ")",
            ]);
            $b3 = Html::button('Test', [
                'id' => "answer-button" . $question['id'],
                'class' => 'btn btn-outline-danger quiz-button',
                'onclick' => "editQuestion('" . addslashes($url) . "')",
            ]);
            ?>
            <div style="display: flex; justify-content: flex-end; align-items: left;">
                <?= $b1 ?>
                <?= $b2 ?>
            </div>
        </div>
    <?php endforeach; ?>
    <hr>
    <?php
    $currentRoute = Yii::$app->controller->getRoute();
    $currentParams = Yii::$app->request->getQueryParams();
    $currentParams['view'] = 'list-blind';
    $newUrl = Url::to(array_merge([$currentRoute], $currentParams));
    echo Html::a('Blind', $newUrl, ['class' => 'btn btn-outline-secondary btn-sm']);
    ?>


</body>

</html>