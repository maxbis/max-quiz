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
            font-size:22px;
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

        /* Full-screen presentation modal styles */
        .presentation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg,rgb(220, 226, 255) 0%,rgb(116, 113, 135) 100%);
            z-index: 9999;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .presentation-content {
            width: 96%;
            max-width: 1400px;
            height: 96%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #333;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .presentation-question {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 40px;
            margin-bottom: 40px;
            line-height: 1.4;
            text-align: left;
            color: #2c3e50;
            flex-shrink: 0;
            white-space: pre-wrap;
        }

        .presentation-question pre {
            margin-top: 20px;
            margin-left: 60px;
            font-size: 2rem;
            color: darkblue;
            border-left: 4px solid lightgray;
            padding-left: 10px;
            text-align: left;
        }

        .presentation-answers {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            flex: 1;
            align-content: center;
        }

        .presentation-answer {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            font-size:1.8rem;
            line-height: 1.5;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .presentation-answer pre {
            margin-top: 20px;
        }

        .presentation-answer pre pre {
            margin-left: 30px;
            font-size: 1.3rem;
            color: darkblue;
            border-left: 2px solid lightgray;
            padding-left: 10px;
        }

        .presentation-answer:hover {
            border-color: #007bff;
            background: #e3f2fd;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
        }

        .presentation-answer.correct {
            border-color: #28a745;
            background: #d4edda;
            color: #155724;
        }

        .presentation-answer.wrong {
            border-color: #dc3545;
            background: #f8d7da;
            color: #721c24;
        }

        .presentation-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            opacity: 0.1;
            transition: opacity 0.3s ease;
        }

        .presentation-controls.visible {
            opacity: 1;
        }

        .presentation-nav-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .presentation-nav-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .presentation-nav-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .presentation-close {
            background: #dc3545;
        }

        .presentation-close:hover {
            background: #c82333;
        }

        .presentation-question-number {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: bold;
        }

        .presentation-show-answer {
            background: #28a745;
            margin: 0 10px;
        }

        .presentation-show-answer:hover {
            background: #218838;
        }

        .question-container {
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .question-container:hover {
            transform: translateY(-2px);
            box-shadow: 5px 5px 15px #888888;
        }

        @media (max-width: 768px) {
            .presentation-content {
                width: 95%;
                padding: 20px;
            }
            
            .presentation-question {
                font-size: 1.8rem;
            }
            
            .presentation-answers {
                grid-template-columns: 1fr;
            }
            
            .presentation-answer {
                font-size: 1.2rem;
                padding: 20px;
            }
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

        // Presentation mode functionality
        let currentQuestionIndex = 0;
        let questions = [];
        let answerShown = false;

        function openPresentationMode(questionIndex) {
            currentQuestionIndex = questionIndex;
            answerShown = false;
            updatePresentationContent();
            document.getElementById('presentationModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closePresentationMode() {
            document.getElementById('presentationModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        function nextQuestion() {
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                answerShown = false;
                updatePresentationContent();
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                answerShown = false;
                updatePresentationContent();
            }
        }

        function showAnswer() {
            answerShown = true;
            updatePresentationContent();
        }

        function updatePresentationContent() {
            const question = questions[currentQuestionIndex];
            const modal = document.getElementById('presentationModal');
            
            // Update question number
            modal.querySelector('.presentation-question-number').textContent = 
                `Question ${currentQuestionIndex + 1} of ${questions.length}`;
            
            // Update question text
            modal.querySelector('.presentation-question').innerHTML = question.question;
            
            // Update answers
            const answersContainer = modal.querySelector('.presentation-answers');
            answersContainer.innerHTML = '';
            
            // Create shuffled answers array
            const answersArray = [];
            for (let i = 1; i <= 6; i++) {
                if (question['a' + i] && question['a' + i] !== '') {
                    answersArray.push({
                        text: question['a' + i],
                        index: i,
                        isCorrect: i === parseInt(question.correct)
                    });
                }
            }
            
            // Shuffle answers for presentation
            const shuffledAnswers = [...answersArray].sort(() => Math.random() - 0.5);
            
            shuffledAnswers.forEach((answer, index) => {
                const answerDiv = document.createElement('div');
                answerDiv.className = 'presentation-answer';
                answerDiv.innerHTML = `
                    <div style="display: flex; align-items: center;">
                        <span style="margin-right: 15px; font-weight: bold; color: #007bff;">
                            ${String.fromCharCode(65 + index)})
                        </span>
                        <span>${answer.text}</span>
                    </div>
                `;
                
                // Add correct/wrong classes if answer is shown
                if (answerShown) {
                    if (answer.isCorrect) {
                        answerDiv.classList.add('correct');
                    } else {
                        answerDiv.classList.add('wrong');
                    }
                }
                
                answersContainer.appendChild(answerDiv);
            });
            
            // Update navigation buttons
            const prevBtn = modal.querySelector('.presentation-nav-btn[onclick*="previous"]');
            const nextBtn = modal.querySelector('.presentation-nav-btn[onclick*="next"]');
            const showAnswerBtn = modal.querySelector('.presentation-nav-btn[onclick*="showAnswer"]');
            
            prevBtn.disabled = currentQuestionIndex === 0;
            nextBtn.disabled = currentQuestionIndex === questions.length - 1;
            showAnswerBtn.style.display = answerShown ? 'none' : 'inline-block';
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('presentationModal');
            if (modal && modal.style.display === 'flex') {
                switch(e.key) {
                    case 'Escape':
                        closePresentationMode();
                        break;
                    case 'ArrowLeft':
                        previousQuestion();
                        break;
                    case 'ArrowRight':
                        nextQuestion();
                        break;
                    case ' ':
                        e.preventDefault();
                        if (!answerShown) {
                            showAnswer();
                        }
                        break;
                }
            }
        });

        // Mouse movement for showing/hiding controls
        document.addEventListener('mousemove', function(e) {
            const modal = document.getElementById('presentationModal');
            if (modal && modal.style.display === 'flex') {
                const controls = modal.querySelector('.presentation-controls');
                const windowHeight = window.innerHeight;
                const mouseY = e.clientY;
                
                // Show controls when mouse is in bottom 20% of screen
                if (mouseY > windowHeight * 0.8) {
                    controls.classList.add('visible');
                } else {
                    controls.classList.remove('visible');
                }
            }
        });

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

    $index = 0;
    foreach ($questions as $question): ?>
        <div style="display:flex;margin-bottom:5px;">
            <div id="<?= 'q' . $question['id'] ?>" style="color: darkblue;font-weight: bold;">
                <?= "Question " . ($index + 1) ?>
            </div>
            <div id="stats<?= $question['id'] ?>" class="stats" style="">
                <?php if (isset($logItems[$question['id']])) {
                    echo ",&nbsp;" . getStats($logItems[$question['id']]);
                } else {
                    echo "-";
                } ?>
            </div>
        </div>

        <div class="question-container row" onclick="openPresentationMode(<?= $index ?>)">
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
            <div style="display: flex; justify-content: flex-end; align-items: left;" onclick="event.stopPropagation();">
                <?= $b1 ?>
                <?= $b2 ?>
            </div>
        </div>
    <?php 
    $index++;
    endforeach; ?>
    <hr>
    <?php
    $currentRoute = Yii::$app->controller->getRoute();
    $currentParams = Yii::$app->request->getQueryParams();
    $currentParams['view'] = 'list-blind';
    $newUrl = Url::to(array_merge([$currentRoute], $currentParams));
    echo Html::a('Blind', $newUrl, ['class' => 'btn btn-outline-secondary btn-sm']);
    ?>

    <!-- Presentation Modal -->
    <div id="presentationModal" class="presentation-modal">
        <div class="presentation-content">
            <div class="presentation-question-number">Question 1 of 1</div>
            
            <div class="presentation-question">
                <!-- Question content will be inserted here -->
            </div>
            
            <div class="presentation-answers">
                <!-- Answers will be inserted here -->
            </div>
            
            <div class="presentation-controls">
                <button class="presentation-nav-btn" onclick="previousQuestion()" disabled>
                    ← Previous
                </button>
                
                <button class="presentation-nav-btn presentation-show-answer" onclick="showAnswer()">
                    Show Answer
                </button>
                
                <button class="presentation-nav-btn" onclick="nextQuestion()">
                    Next →
                </button>
                
                <button class="presentation-nav-btn presentation-close" onclick="closePresentationMode()">
                    Close (ESC)
                </button>
            </div>
        </div>
    </div>

    <script>
        // Initialize questions array for presentation mode
        questions = <?= json_encode($questions) ?>;
    </script>

</body>

</html>