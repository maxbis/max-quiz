<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

// Include the function for rendering questions with allowed HTML tags
require_once Yii::getAlias('@app/views/include/functions.php');

$this->title = 'Edit Question Labels - ' . Html::encode($quiz['name']);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= Html::encode($this->title) ?></title>
    <!-- SortableJS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .header .quiz-info {
            color: #666;
            font-size: 14px;
        }

        .question-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            cursor: move;
        }

        .question-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border-color: #4CAF50;
        }

        .question-item.sortable-ghost {
            opacity: 0.4;
            background-color: #f0f0f0;
        }

        .question-item.sortable-drag {
            opacity: 0.8;
            transform: rotate(2deg);
        }

        .drag-handle {
            flex: 0 0 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 20px;
            cursor: grab;
            user-select: none;
            align-self: center;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .question-number {
            flex: 0 0 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            font-size: 14px;
            align-self: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }

        .question-number:hover {
            background-color: #45a049;
            transform: scale(1.1);
            box-shadow: 0 2px 6px rgba(76, 175, 80, 0.4);
        }

        .question-content {
            flex: 1;
            min-width: 0;
        }

        .question-preview {
            max-height: 120px; /* Approximately 5 lines at 24px line-height */
            overflow-y: auto;
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            padding: 10px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 24px;
            word-wrap: break-word;
            margin-bottom: 10px;
        }

        .question-preview pre {
            background-color: #e8e8e8;
            border: 1px solid #ccc;
            padding: 10px;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            overflow-x: auto;
            margin: 5px 0;
        }

        .question-preview code {
            background-color: #e8e8e8;
            padding: 2px 6px;
            font-family: monospace;
            border-radius: 3px;
        }

        .question-preview b {
            font-weight: bold;
        }

        .question-preview i {
            font-style: italic;
        }

        .question-preview::-webkit-scrollbar {
            width: 8px;
        }

        .question-preview::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .question-preview::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .question-preview::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .question-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }

        .label-input-container {
            flex: 0 0 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .label-input-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .label-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .label-input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .submit-container {
            position: sticky;
            bottom: 0;
            background-color: #fff;
            padding: 20px;
            border-top: 2px solid #ddd;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .btn-back {
            background-color: #666;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 15px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background-color: #555;
        }

        .flash-message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .flash-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .flash-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .no-questions {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 18px;
        }

        .question-counter {
            background-color: #f0f0f0;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }

        .save-order-notice {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            display: none;
        }

        .save-order-notice.show {
            display: block;
        }

        .order-saved-notice {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            display: none;
        }

        .order-saved-notice.show {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="quiz-info">
            Quiz: <strong><?= Html::encode($quiz['name']) ?></strong> (ID: <?= $quiz['id'] ?>)
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="flash-message flash-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="flash-message flash-error">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($questions)): ?>
        <div class="no-questions">
            No active questions found for this quiz.
        </div>
        <div style="text-align: center;">
            <?= Html::a('Back to Quiz List', ['index'], ['class' => 'btn-back']) ?>
        </div>
    <?php else: ?>
        <div class="question-counter">
            Total Questions: <strong><?= count($questions) ?></strong> | 
            <span style="color: #4CAF50;">💡 Tip: Drag questions to reorder them</span>
        </div>

        <div id="orderSavedNotice" class="order-saved-notice">
            ✓ Question order saved automatically
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'post',
            'action' => ['edit-labels', 'id' => $quiz['id']],
        ]); ?>

            <div id="questionsList">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item" data-question-id="<?= $question['id'] ?>">
                    <div class="drag-handle" title="Drag to reorder">
                        ⋮⋮
                    </div>
                    <?= Html::a(
                        ($index + 1),
                        ['question/view', 'id' => $question['id'], 'quiz_id' => $quiz['id']],
                        [
                            'class' => 'question-number',
                            'title' => 'View question details',
                            'target' => '_blank'
                        ]
                    ) ?>
                    <div class="question-content">
                        <div class="question-meta">
                            Question ID: <?= $question['id'] ?>
                        </div>
                        <div class="question-preview" title="Scroll to see full question">
                            <?= escapeHtmlExceptTags($question['question']) ?>
                        </div>
                    </div>
                    <div class="label-input-container">
                        <label class="label-input-label" for="label-<?= $question['id'] ?>">
                            Label:
                        </label>
                        <input 
                            type="text" 
                            id="label-<?= $question['id'] ?>" 
                            name="labels[<?= $question['id'] ?>]" 
                            class="label-input" 
                            value="<?= Html::encode($question['label'] ?? '') ?>"
                            maxlength="100"
                            placeholder="Enter label (max 100 chars)"
                        />
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <div class="submit-container">
                <button type="submit" class="btn-submit">
                    💾 Save All Labels
                </button>
                <?= Html::a('Cancel', ['index'], ['class' => 'btn-back']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>

    <script>
        // Initialize SortableJS on the questions list
        const questionsList = document.getElementById('questionsList');
        
        if (questionsList) {
            const sortable = new Sortable(questionsList, {
                animation: 150,
                handle: '.drag-handle', // Only allow dragging by the handle
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                
                onEnd: function(evt) {
                    // Update question numbers after drag
                    updateQuestionNumbers();
                    
                    // Save the new order via AJAX
                    saveQuestionOrder();
                }
            });
        }

        function updateQuestionNumbers() {
            const items = document.querySelectorAll('.question-item');
            items.forEach((item, index) => {
                const numberElement = item.querySelector('.question-number');
                if (numberElement) {
                    numberElement.textContent = index + 1;
                }
            });
        }

        function saveQuestionOrder() {
            const items = document.querySelectorAll('.question-item');
            const orderData = [];
            
            items.forEach((item, index) => {
                const questionId = item.getAttribute('data-question-id');
                orderData.push({
                    question_id: questionId,
                    order: index + 1
                });
            });

            // Send AJAX request to save order
            fetch('<?= \yii\helpers\Url::to(['quiz/update-question-order', 'id' => $quiz['id']]) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                },
                body: JSON.stringify({
                    order: orderData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                    showOrderSavedNotice();
                } else {
                    alert('Error saving order: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save question order. Please try again.');
            });
        }

        function showOrderSavedNotice() {
            const notice = document.getElementById('orderSavedNotice');
            notice.classList.add('show');
            
            // Hide after 3 seconds
            setTimeout(() => {
                notice.classList.remove('show');
            }, 3000);
        }
    </script>

</body>
</html>

