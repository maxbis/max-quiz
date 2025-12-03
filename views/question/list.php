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
            max-height: 96vh;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            color: #333;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow-y: auto;
        }

        .presentation-question {
            font-size: clamp(1rem, 2.5vh, 1.5rem);
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 20px;
            line-height: 1.4;
            text-align: left;
            color: #2c3e50;
            flex-shrink: 1;
            white-space: pre-wrap;
        }

        .presentation-question pre {
            margin-top: 20px;
            margin-left: 20px;
            font-size: clamp(1rem, 3vh, 2rem);
            color: darkblue;
            border-left: 4px solid lightgray;
            padding-left: 10px;
            text-align: left;
            overflow-x: auto;
            max-width: 100%;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .presentation-answers {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            flex-shrink: 1;
        }

        .presentation-answer {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            font-size: clamp(0.9rem, 2.2vh, 1.8rem);
            line-height: 1.5;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .presentation-answer pre {
            margin-top: 10px;
            margin-bottom: 10px;
            font-size: clamp(0.8rem, 2vh, 1.3rem);
            overflow-x: auto;
            max-width: 100%;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .presentation-answer pre pre {
            margin-left: 20px;
            font-size: clamp(0.8rem, 2vh, 1.3rem);
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
            transition: transform 0.2s ease;
        }

        .question-container:hover {
            transform: translateY(-2px);
            box-shadow: 5px 5px 15px #888888;
        }

        .clickable-question {
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 10px;
            border-radius: 8px;
            position: relative;
        }

        .clickable-question:hover {
            background-color: rgba(0, 123, 255, 0.1);
            border: 2px solid rgba(0, 123, 255, 0.3);
            transform: translateY(-1px);
        }

        .clickable-question::after {
            content: "📺";
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 16px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .clickable-question:hover::after {
            opacity: 1;
        }

        /* Custom scrollbar for presentation mode */
        .presentation-content::-webkit-scrollbar {
            width: 10px;
        }

        .presentation-content::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .presentation-content::-webkit-scrollbar-thumb {
            background: rgba(0, 123, 255, 0.5);
            border-radius: 10px;
        }

        .presentation-content::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 123, 255, 0.7);
        }

        .presentation-content {
            scroll-behavior: smooth;
        }

        h1 a:hover {
            color: #0a58ca !important;
            text-decoration: underline !important;
            cursor: pointer;
        }

        /* Dim buttons by default, show on hover */
        .question-buttons {
            opacity: 0.2;
            transition: opacity 0.3s ease;
        }

        .question-buttons:hover {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .presentation-content {
                width: 95%;
                padding: 15px;
            }
            
            .presentation-question {
                margin-top: 10px;
                margin-bottom: 15px;
            }

            .presentation-question pre {
                margin-left: 20px;
            }
            
            .presentation-answers {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .presentation-answer {
                padding: 15px;
            }

            .presentation-nav-btn {
                padding: 10px 15px;
                font-size: 0.9rem;
                min-width: 80px;
            }
        }

        @media (max-height: 700px) {
            .presentation-content {
                padding: 20px;
            }

            .presentation-question {
                margin-top: 10px;
                margin-bottom: 15px;
            }

            .presentation-answers {
                gap: 10px;
            }

            .presentation-answer {
                padding: 15px;
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
                
                // Create the container div
                const containerDiv = document.createElement('div');
                containerDiv.style.cssText = 'display: flex; align-items: flex-start; gap: 15px;';
                
                // Create the label span
                const labelSpan = document.createElement('span');
                labelSpan.style.cssText = 'font-weight: bold; color: #007bff; flex-shrink: 0; padding-top: 2px;';
                labelSpan.textContent = `${String.fromCharCode(65 + index)})`;
                
                // Create the content div
                const contentDiv = document.createElement('div');
                contentDiv.style.cssText = 'flex: 1; overflow-wrap: break-word;';
                contentDiv.innerHTML = answer.text; // Use innerHTML here to preserve <pre> tags
                
                // Assemble the structure
                containerDiv.appendChild(labelSpan);
                containerDiv.appendChild(contentDiv);
                answerDiv.appendChild(containerDiv);
                
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
    <?php
    // Determine whether to use random or deterministic answer order
    $useRandomAnswers = Yii::$app->request->get('random_answers', '0') === '1';
    $currentParams = Yii::$app->request->getQueryParams();
    ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin: 0;">
            <?= Html::a($quiz['name'], ['question/index', 'quiz_id' => $quiz['id']], [
                'style' => 'color: inherit; text-decoration: none;',
                'title' => 'Go to quiz questions overview'
            ]); ?>
        </h1>

        <form method="get" style="margin: 0;">
            <?php foreach ($currentParams as $key => $value): ?>
                <?php if ($key === 'random_answers') continue; ?>
                <input type="hidden" name="<?= Html::encode($key) ?>" value="<?= Html::encode($value) ?>">
            <?php endforeach; ?>

            <label style="font-size: 0.9rem;">
                <input
                    type="checkbox"
                    name="random_answers"
                    value="1"
                    <?= $useRandomAnswers ? 'checked' : '' ?>
                    onchange="this.form.submit()"
                    title="Answers are randominzed in a deterministic way, unless the checkbox is checked."
                >
                Random answer order
            </label>
        </form>
    </div>


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

        <div class="question-container row">
            <div class="_col">
                <form class="answers">
                    <div class="question clickable-question" id="question<?= $question['id'] ?>" onclick="openPresentationMode(<?= $index ?>)" title="Click to open presentation mode">
<?= escapeHtmlExceptTags($question['question'] ); ?>
                    </div>
            </div>

            <div class="_col" style="border-top:1px dashed gray;margin-top:80px;" onclick="event.stopPropagation();">

                <?php
                $array = ['1','2','3','4','5','6'];

                // By default use deterministic order seeded per question;
                // when "Random answer order" checkbox is checked, use true random.
                if ($useRandomAnswers) {
                    shuffle($array);
                } else {
                    $seed = crc32($question['id']);
                    mt_srand($seed);
                    shuffle($array);
                }
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
            // Create return URL for the View button
            $returnUrl = Url::to(['question/list', 'quiz_id' => $quiz['id']]);
            
            // Create View button (left-aligned)
            $viewUrl = Yii::$app->urlManager->createUrl([
                '/question/view', 
                'id' => $question['id'], 
                'quiz_id' => $quiz['id'],
                'returnUrl' => $returnUrl
            ]);
            $bView = Html::a('View', $viewUrl, [
                'title' => 'View this question',
                'class' => 'btn btn-outline-success quiz-button',
            ]);
            
            // Create Edit button
            $url = Yii::$app->urlManager->createUrl(['/question/update', 'id' => $question['id']]);
            $b1 = Html::a('Edit', $url, [
                'title' => 'Edit',
                'class' => 'btn btn-outline-primary quiz-button',
            ]);
            
            // Create Answer button
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
            <div class="question-buttons" style="display: flex; justify-content: space-between; align-items: left;" onclick="event.stopPropagation();">
                <div>
                    <?= $bView ?>
                </div>
                <div>
                    <?= $b1 ?>
                    <?= $b2 ?>
                </div>
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
        const rawQuestions = <?= json_encode($questions) ?>;
        
        /**
         * JavaScript equivalent of PHP escapeHtmlExceptTags() function
         * Escapes ALL HTML tags for security, then selectively un-escapes allowed formatting tags
         * 
         * This matches the exact algorithm used in views/include/functions.php:
         * 1. Remove unwanted tags (if specified)
         * 2. Escape ALL HTML using htmlspecialchars equivalent
         * 3. Un-escape ONLY allowed tags (pre, code, i, b by default)
         * 
         * @param {string} html - The HTML string to process
         * @param {array} deleteTags - Tags to remove completely (optional)
         * @param {array} allowedTags - Tags that should be rendered as HTML (default: pre, code, i, b)
         * @returns {string} Safely escaped HTML with allowed tags preserved
         */
        function escapeHtmlExceptTags(html, deleteTags = [], allowedTags = ['pre', 'code', 'i', 'b']) {
            if (!html) return html;
            
            // Step 1: Remove unwanted tags completely
            deleteTags.forEach(tag => {
                const startTagRegex = new RegExp('<' + tag + '>', 'gi');
                const endTagRegex = new RegExp('</' + tag + '>', 'gi');
                html = html.replace(startTagRegex, '').replace(endTagRegex, '');
            });
            
            // Step 2: Escape ALL HTML characters (equivalent to PHP's htmlspecialchars)
            // This makes everything safe by default - even malicious scripts become harmless text
            let escapedHtml = html
                .replace(/&/g, '&amp;')     // Must be first!
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
            
            // Step 3: Un-escape ONLY the allowed tags so they render as actual HTML
            // For each allowed tag, replace the escaped version back to real HTML
            allowedTags.forEach(tag => {
                const escapedStartTag = '&lt;' + tag + '&gt;';
                const escapedEndTag = '&lt;/' + tag + '&gt;';
                // Use global replacement to handle multiple occurrences
                const startTagRegex = new RegExp(escapedStartTag, 'gi');
                const endTagRegex = new RegExp(escapedEndTag, 'gi');
                escapedHtml = escapedHtml.replace(startTagRegex, '<' + tag + '>');
                escapedHtml = escapedHtml.replace(endTagRegex, '</' + tag + '>');
            });
            
            return escapedHtml;
        }
        
        // Process all questions using the same algorithm as the PHP view
        questions = rawQuestions.map(question => {
            const escapedQuestion = { ...question };
            
            // Escape HTML in question text
            // Allows: <pre>, <code>, <i>, <b> for formatting
            // Blocks: <script>, <iframe>, and all other HTML tags
            if (escapedQuestion.question) {
                escapedQuestion.question = escapeHtmlExceptTags(escapedQuestion.question);
            }
            
            // Escape HTML in all answer fields
            // For answers, we delete <pre> tags to prevent nested code blocks
            // But still allow code formatting via the parent function
            for (let i = 1; i <= 6; i++) {
                if (escapedQuestion['a' + i]) {
                    escapedQuestion['a' + i] = escapeHtmlExceptTags(escapedQuestion['a' + i], ['pre']);
                }
            }
            
            return escapedQuestion;
        });
    </script>

</body>

</html>