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
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-overlay.show {
            display: block;
        }

        .modal-dialog {
            background-color: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1001;
            pointer-events: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-instructions {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1565c0;
        }

        .label-sort-item {
            background-color: #f9f9f9;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: move;
            transition: all 0.3s ease;
        }

        .label-sort-item:hover {
            background-color: #fff;
            border-color: #2196F3;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
        }

        .label-sort-item.sortable-ghost {
            opacity: 0.4;
            background-color: #e3f2fd;
        }

        .label-sort-item.sortable-drag {
            opacity: 0.8;
            transform: rotate(1deg);
        }

        .label-sort-handle {
            flex: 0 0 30px;
            color: #999;
            font-size: 24px;
            cursor: grab;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            touch-action: none;
        }

        .label-sort-handle:active {
            cursor: grabbing;
        }
        
        .label-sort-handle:hover {
            color: #666;
        }

        .label-sort-number {
            flex: 0 0 30px;
            background-color: #2196F3;
            color: white;
            font-weight: bold;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .label-sort-text {
            flex: 1;
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .label-sort-text.empty-label {
            color: #999;
            font-style: italic;
        }

        .label-sort-count {
            flex: 0 0 auto;
            background-color: #e0e0e0;
            color: #666;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 2px solid #ddd;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .no-labels-message {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
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

        /* Button styles matching quiz/update */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            border: 2px solid;
            transition: all 0.2s ease;
            cursor: pointer;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 123, 255, 0.3);
        }

        .quiz-button {
            font-size: 14px;
            min-width: 80px;
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
            <?= Html::a('⬅️ Back', ['question/index', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-primary quiz-button']) ?>
        </div>
    <?php else: ?>
        <div class="question-counter">
            <div>
                Total Questions: <strong><?= count($questions) ?></strong> | 
                <span style="color: #4CAF50;">💡 Tip: Drag questions to reorder them</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <?= Html::a('⬅️ Back', ['question/index', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-primary quiz-button', 'style' => 'margin: 0;']) ?>
                <button type="button" class="btn-sort-labels" onclick="openLabelSortDialog()">
                    🔤 Sort by Labels
                </button>
                <button type="submit" form="labelsForm" class="btn-submit" style="margin: 0;">
                    💾 Save
                </button>
            </div>
        </div>

        <div id="orderSavedNotice" class="order-saved-notice">
            ✓ Question order saved automatically
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'post',
            'action' => ['edit-labels', 'id' => $quiz['id']],
            'id' => 'labelsForm',
        ]); ?>

            <div id="questionsList">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item" data-question-id="<?= $question['id'] ?>">
                    <div class="drag-handle" title="Drag to reorder">
                        ⋮⋮
                    </div>
                    <?= Html::a(
                        ($index + 1),
                        ['question/view', 'id' => $question['id'], 'quiz_id' => $quiz['id'], 'returnUrl' => 'edit-labels'],
                        [
                            'class' => 'question-number',
                            'title' => 'View question details',
                            // 'target' => '_blank'
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


        <?php ActiveForm::end(); ?>
    <?php endif; ?>

    <!-- Label Sort Dialog -->
    <div id="labelSortModal" class="modal-overlay">
        <div class="modal-dialog">
            <div class="modal-header">
                <h2>🔤 Sort Questions by Labels</h2>
                <button type="button" class="modal-close" onclick="closeLabelSortDialog()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-instructions">
                    💡 Drag and drop labels to define the sort order. Questions will be sorted by label order first, then by ID within each label.
                </div>
                <div id="labelSortList">
                    <!-- Labels will be dynamically inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeLabelSortDialog()">Cancel</button>
                <button type="button" class="btn-modal btn-modal-primary" onclick="applyLabelSort()">OK - Apply Sort</button>
            </div>
        </div>
    </div>

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

        // ===== Label Sort Dialog Functions =====
        let labelSortable = null;
        const STORAGE_KEY = 'quiz_<?= $quiz['id'] ?>_label_sort_order';

        function openLabelSortDialog() {
            // Get all unique labels from the questions
            const labelCounts = {};
            const items = document.querySelectorAll('.question-item');
            
            items.forEach(item => {
                const labelInput = item.querySelector('.label-input');
                const label = labelInput ? (labelInput.value.trim() || '(No Label)') : '(No Label)';
                
                if (!labelCounts[label]) {
                    labelCounts[label] = 0;
                }
                labelCounts[label]++;
            });

            // Get saved label order from localStorage
            let savedOrder = [];
            try {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved) {
                    savedOrder = JSON.parse(saved);
                }
            } catch (e) {
                console.error('Error loading saved label order:', e);
            }

            // Create sorted array of labels
            const labels = Object.keys(labelCounts);
            
            // Sort labels: first by saved order, then alphabetically for new labels
            labels.sort((a, b) => {
                const indexA = savedOrder.indexOf(a);
                const indexB = savedOrder.indexOf(b);
                
                // Both in saved order - use saved order
                if (indexA !== -1 && indexB !== -1) {
                    return indexA - indexB;
                }
                // Only A in saved order - A comes first
                if (indexA !== -1) {
                    return -1;
                }
                // Only B in saved order - B comes first
                if (indexB !== -1) {
                    return 1;
                }
                // Neither in saved order - sort alphabetically
                return a.localeCompare(b);
            });

            // Populate the modal with labels
            const labelSortList = document.getElementById('labelSortList');
            labelSortList.innerHTML = '';

            if (labels.length === 0) {
                labelSortList.innerHTML = '<div class="no-labels-message">No labels found</div>';
            } else {
                labels.forEach((label, index) => {
                    const item = document.createElement('div');
                    item.className = 'label-sort-item';
                    item.setAttribute('data-label', label);
                    
                    const isEmptyLabel = label === '(No Label)';
                    
                    item.innerHTML = `
                        <div class="label-sort-handle">⋮⋮</div>
                        <div class="label-sort-number">${index + 1}</div>
                        <div class="label-sort-text ${isEmptyLabel ? 'empty-label' : ''}">${escapeHtml(label)}</div>
                        <div class="label-sort-count">${labelCounts[label]} question${labelCounts[label] > 1 ? 's' : ''}</div>
                    `;
                    
                    labelSortList.appendChild(item);
                });
            }

            // Show the modal first
            document.getElementById('labelSortModal').classList.add('show');
            
            // Then initialize SortableJS after modal is visible
            // Use setTimeout to ensure DOM is fully ready and rendered
            setTimeout(() => {
                if (labelSortable) {
                    labelSortable.destroy();
                }
                
                labelSortable = new Sortable(labelSortList, {
                    animation: 150,
                    handle: '.label-sort-handle',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function() {
                        updateLabelNumbers();
                    }
                });
            }, 50);
        }

        function closeLabelSortDialog() {
            document.getElementById('labelSortModal').classList.remove('show');
        }

        function updateLabelNumbers() {
            const items = document.querySelectorAll('.label-sort-item');
            items.forEach((item, index) => {
                const numberElement = item.querySelector('.label-sort-number');
                if (numberElement) {
                    numberElement.textContent = index + 1;
                }
            });
        }

        function applyLabelSort() {
            // Get the sorted labels from the modal
            const labelItems = document.querySelectorAll('.label-sort-item');
            const sortedLabels = [];
            
            labelItems.forEach(item => {
                const label = item.getAttribute('data-label');
                sortedLabels.push(label);
            });

            // Save the sort order to localStorage
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(sortedLabels));
            } catch (e) {
                console.error('Error saving label order:', e);
            }

            // Sort the questions based on the label order
            const questionsList = document.getElementById('questionsList');
            const questionItems = Array.from(document.querySelectorAll('.question-item'));
            
            // Create a map of questions grouped by label
            const questionsByLabel = {};
            
            questionItems.forEach(item => {
                const labelInput = item.querySelector('.label-input');
                const label = labelInput ? (labelInput.value.trim() || '(No Label)') : '(No Label)';
                const questionId = parseInt(item.getAttribute('data-question-id'));
                
                if (!questionsByLabel[label]) {
                    questionsByLabel[label] = [];
                }
                
                questionsByLabel[label].push({
                    element: item,
                    id: questionId
                });
            });

            // Sort questions within each label by ID
            Object.keys(questionsByLabel).forEach(label => {
                questionsByLabel[label].sort((a, b) => a.id - b.id);
            });

            // Build the sorted array of question elements
            const sortedQuestions = [];
            
            // First add questions from labels in the defined order
            sortedLabels.forEach(label => {
                if (questionsByLabel[label]) {
                    questionsByLabel[label].forEach(q => sortedQuestions.push(q.element));
                    delete questionsByLabel[label]; // Mark as processed
                }
            });

            // Then add questions from any remaining labels (new labels not in sort order)
            Object.keys(questionsByLabel).sort().forEach(label => {
                questionsByLabel[label].forEach(q => sortedQuestions.push(q.element));
            });

            // Clear and repopulate the questions list
            questionsList.innerHTML = '';
            sortedQuestions.forEach(element => {
                questionsList.appendChild(element);
            });

            // Update question numbers
            updateQuestionNumbers();

            // Save the new order via AJAX
            saveQuestionOrder();

            // Close the modal
            closeLabelSortDialog();

            // Show success message
            showOrderSavedNotice();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal when clicking on the overlay (outside the dialog)
        document.getElementById('labelSortModal').addEventListener('click', function(e) {
            // Check if click is on the overlay itself (not on dialog or its children)
            if (e.target.classList.contains('modal-overlay')) {
                closeLabelSortDialog();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('labelSortModal');
                if (modal.classList.contains('show')) {
                    closeLabelSortDialog();
                }
            }
        });
    </script>

</body>
</html>

