<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Question $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    /* Modern Question Form Styling */
    
    .form-container {
        max-width: 1400px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .form-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 25px 30px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .form-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
    }
    
    .form-header .question-id {
        opacity: 0.9;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .form-body {
        padding: 30px;
    }
    
    .section {
        margin-bottom: 35px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .section-title .icon {
        margin-right: 10px;
        font-size: 1.3rem;
    }
    
    .question-field {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border: 2px solid #e9ecef;
        transition: border-color 0.3s ease;
    }
    
    .question-field:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .answer-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 20px;
    }
    
    .answer-field {
        background: white;
        border-radius: 8px;
        padding: 15px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .answer-field:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .answer-field.correct-answer {
        border-color: #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }
    
    .answer-field.correct-answer::before {
        content: "✓";
        position: absolute;
        top: 40px;
        right: 10px;
        color: #28a745;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    .answer-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .answer-number {
        background: #007bff;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .control-fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }
    
    .control-field {
        display: flex;
        flex-direction: column;
    }
    
    .control-field label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    
    .control-field input {
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        transition: border-color 0.3s ease;
        font-size: 1rem;
    }
    
    .control-field input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .quiz-assignments-section {
        background: white;
        border-radius: 8px;
        padding: 25px;
        border: 2px solid #e9ecef;
    }
    
    .assignment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .assignment-count {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .assignment-controls {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .quiz-button {
        font-size: 14px;
        padding: 10px 20px;
        min-width: 120px;
        margin: 5px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .quiz-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: #28a745;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #007bff;
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        border-color: #ffc107;
        color: #212529;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-color: #dc3545;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding: 30px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }
    
    .character-count {
        font-size: 0.8rem;
        color: #6c757d;
        text-align: right;
        margin-top: 5px;
    }
    
    .character-count.warning {
        color: #ffc107;
    }
    
    .character-count.danger {
        color: #dc3545;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: 12px;
        transition: all 0.3s ease;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        outline: none;
    }
    
    .form-control[style*="font-family: monospace"] {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        background: #f8f9fa;
    }
    
    .quiz-assignment-item {
        transition: all 0.3s ease;
        border-radius: 6px;
    }
    
    .quiz-assignment-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .quiz-assignment-archived {
        opacity: 0.6;
        background-color: #f5f5f5 !important;
        border-color: #d0d0d0 !important;
    }
    
    .quiz-assignment-archived:hover {
        opacity: 0.75;
        background-color: #ececec !important;
    }
    
    @media (max-width: 768px) {
        .answer-grid {
            grid-template-columns: 1fr;
        }
        
        .control-fields {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .assignment-controls {
            flex-direction: column;
        }
    }
    
    @media (max-width: 1024px) and (min-width: 769px) {
        .answer-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>


<div class="question-form">
    <?php
        function shortText($text, $maxLength = 12)
        {
            if (strlen($text) > $maxLength) {
                return substr($text, 0, $maxLength) . '...';
            }
            return $text;
        }
        $form = ActiveForm::begin();
    ?>

    <div class="form-container">
        <!-- Form Header -->
        <div class="form-header">
            <h2>📝 Update Question</h2>
            <div class="question-id">Question ID: <?= $model->id ?></div>
        </div>

        <div class="form-body">
            <!-- Question Section -->
            <div class="section">
                <div class="section-title">
                    <span class="icon">❓</span>
                    Question
                </div>
                <div class="question-field">
                    <?= $form->field($model, 'question')->textarea([
                        'rows' => 8,
                        'style' => 'font-family: monospace; border: none; background: transparent; resize: vertical; min-height: 120px;',
                        'maxlength' => true,
                        'placeholder' => 'Enter your question here...'
                    ])->label(false) ?>
                    <div class="character-count" id="question-count">0 characters</div>
                </div>
            </div>


            <!-- Answers Section -->
            <div class="section">
                <div class="section-title">
                    <span class="icon">📝</span>
                    Answer Options
                </div>
                <div class="answer-grid">
                    <?php
                    $answers = ['a1', 'a2', 'a3', 'a4', 'a5', 'a6'];
                    $correctAnswer = $model->correct;
                    foreach ($answers as $index => $answerField):
                        $answerNumber = $index + 1;
                        $isCorrect = ($correctAnswer == $answerNumber);
                    ?>
                    <div class="answer-field <?= $isCorrect ? 'correct-answer' : '' ?>" data-answer="<?= $answerNumber ?>">
                        <div class="answer-label">
                            <span>Answer #<?= $answerNumber ?></span>
                            <div class="answer-number"><?= $answerNumber ?></div>
                        </div>
                        <?= $form->field($model, $answerField)->textarea([
                            'rows' => 3,
                            'style' => 'font-family: monospace; border: none; background: transparent; resize: vertical; min-height: 80px;',
                            'maxlength' => true,
                            'placeholder' => 'Enter answer option ' . $answerNumber . '...'
                        ])->label(false) ?>
                        <div class="character-count" id="answer-<?= $answerNumber ?>-count">0 characters</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Control Fields Section -->
            <div class="section">
                <div class="section-title">
                    <span class="icon">⚙️</span>
                    Settings
                </div>
                <div class="control-fields">
                    <div class="control-field">
                        <label for="question-correct">Correct Answer</label>
                        <?= $form->field($model, 'correct')->textInput([
                            'type' => 'number',
                            'min' => '1',
                            'max' => '6',
                            'placeholder' => '1-6',
                            'title' => 'Which answer is correct? (1-6)'
                        ])->label(false) ?>
                    </div>

                    <?php if (isset($quiz_id) && $quiz_id): ?>
                    <div class="control-field">
                        <label for="quiz_order">Sort Order</label>
                        <?= Html::textInput('quiz_order', $currentOrder, [
                            'class' => 'form-control',
                            'type' => 'number',
                            'min' => '0',
                            'placeholder' => '0',
                            'title' => 'Order of this question in the quiz (lower numbers appear first)'
                        ]) ?>
                    </div>
                    <?php endif; ?>

                    <div class="control-field">
                        <label for="question-label">Category Label</label>
                        <?= $form->field($model, 'label')->textInput([
                            'placeholder' => 'e.g., HTML, CSS, JavaScript',
                            'title' => 'Category or topic label for this question'
                        ])->label(false) ?>
                    </div>
                </div>
            </div>

            <!-- Quiz Assignments Section -->
            <?php if (isset($questionLinks) && $questionLinks != '' ) { ?>
            <div class="section">
                <div class="section-title">
                    <span class="icon">📚</span>
                    Quiz Assignments
                </div>
                <div class="quiz-assignments-section">
                    <div class="assignment-header">
                        <div class="assignment-count" id="assignmentCount">
                            <?= count(array_filter($questionLinks, function($link) { return $link['active']; })) ?> of <?= count($questionLinks) ?> selected
                        </div>
                        <div class="assignment-controls">
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#quizAssignments" aria-expanded="false">
                                📋 Manage Assignments
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleAllQuizzes(true)">✓ Select All</button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllQuizzes(false)">✗ Deselect All</button>
                            
                            <?php 
                            $showArchived = $show_archived ?? 0;
                            $currentUrl = Yii::$app->request->url;
                            $baseUrl = strtok($currentUrl, '?');
                            $params = Yii::$app->request->queryParams;
                            ?>
                            
                            <?php if ($showArchived): ?>
                                <?= Html::a('📦 Hide Archived', array_merge(['question/update'], ['id' => $model->id], array_diff_key($params, ['show_archived' => '']), ['show_archived' => 0]), [
                                    'class' => 'btn btn-outline-secondary btn-sm',
                                    'title' => 'Show only active quizzes'
                                ]) ?>
                            <?php else: ?>
                                <?= Html::a('📂 Show Archived', array_merge(['question/update'], ['id' => $model->id], $params, ['show_archived' => 1]), [
                                    'class' => 'btn btn-outline-info btn-sm',
                                    'title' => 'Show all quizzes including archived'
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="collapse" id="quizAssignments">
                        <div class="card card-body">
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-sm" placeholder="🔍 Search quizzes..." onkeyup="searchQuizzes(this)">
                            </div>
                            
                            <div class="quiz-assignments-list" style="max-height: 350px; overflow-y: auto;">
                                <?php foreach ($questionLinks as $link): ?>
                                    <?php 
                                    $isArchived = isset($link['archived']) && $link['archived'];
                                    $itemClass = $isArchived ? 'quiz-assignment-item quiz-assignment-archived border rounded p-2 mb-2' : 'quiz-assignment-item border rounded p-2 mb-2';
                                    ?>
                                    <div class="<?= $itemClass ?>" data-quiz-name="<?= strtolower($link['name']) ?>">
                                        <div class="form-check d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center flex-grow-1">
                                                <?= Html::hiddenInput('questionLinks[' . $link['id'] . ']', 0) ?>
                                                <?= Html::checkbox('questionLinks[' . $link['id'] . ']', $link['active'], [
                                                    'id' => 'quiz_assignment_' . $link['id'],
                                                    'class' => 'form-check-input me-2 quiz-assignment-checkbox',
                                                    'value' => 1,
                                                    'onchange' => 'updateAssignmentCount()'
                                                ]) ?>
                                                <label class="form-check-label flex-grow-1" for="quiz_assignment_<?= $link['id'] ?>">
                                                    <strong><?= Html::encode($link['name']) ?></strong>
                                                    <?php if ($isArchived): ?>
                                                        <span class="badge bg-secondary ms-1" style="font-size: 9px;">ARCHIVED</span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                            <span class="badge <?= $link['active'] ? 'bg-success' : 'bg-secondary' ?> ms-2">
                                                <?= $link['active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .quiz-assignment-item:hover {
                    background-color: #f8f9fa;
                }
                
                .quiz-assignment-item .form-check-label {
                    cursor: pointer;
                    width: 100%;
                }
                
                .quiz-assignments-list {
                    background-color: #ffffff;
                }
                
                /* Archived quiz styling */
                .quiz-assignment-archived {
                    opacity: 0.6;
                    background-color: #f5f5f5 !important;
                    border-color: #d0d0d0 !important;
                }
                
                .quiz-assignment-archived:hover {
                    opacity: 0.75;
                    background-color: #ececec !important;
                }
                
                .quiz-assignment-archived .form-check-label {
                    color: #6c757d;
                }
            </style>

            <script>
                function updateAssignmentCount() {
                    const checkedBoxes = document.querySelectorAll('.quiz-assignment-checkbox:checked');
                    const totalBoxes = document.querySelectorAll('.quiz-assignment-checkbox');
                    document.getElementById('assignmentCount').textContent = 
                        checkedBoxes.length + ' of ' + totalBoxes.length + ' selected';
                    
                    // Update individual badges
                    document.querySelectorAll('.quiz-assignment-checkbox').forEach(checkbox => {
                        const quizId = checkbox.id.replace('quiz_assignment_', '');
                        const badge = checkbox.closest('.quiz-assignment-item').querySelector('.badge');
                        if (checkbox.checked) {
                            badge.textContent = 'Active';
                            badge.className = 'badge bg-success ms-2';
                        } else {
                            badge.textContent = 'Inactive';
                            badge.className = 'badge bg-secondary ms-2';
                        }
                    });
                }

                function toggleAllQuizzes(selectAll) {
                    const visibleCheckboxes = document.querySelectorAll('.quiz-assignment-item:not([style*="display: none"]) .quiz-assignment-checkbox');
                    visibleCheckboxes.forEach(checkbox => checkbox.checked = selectAll);
                    updateAssignmentCount();
                }

                function searchQuizzes(input) {
                    const searchTerm = input.value.toLowerCase();
                    const quizItems = document.querySelectorAll('.quiz-assignment-item');
                    
                    quizItems.forEach(item => {
                        const quizName = item.getAttribute('data-quiz-name');
                        if (quizName.includes(searchTerm)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }

                // Initialize count on page load
                document.addEventListener('DOMContentLoaded', updateAssignmentCount);
            </script>
        <?php } ?>

        <script>
            // Character counting and visual feedback
            document.addEventListener('DOMContentLoaded', function() {
                // Question character count
                const questionField = document.querySelector('textarea[name="Question[question]"]');
                const questionCount = document.getElementById('question-count');
                
                if (questionField && questionCount) {
                    updateCharacterCount(questionField, questionCount);
                    questionField.addEventListener('input', function() {
                        updateCharacterCount(this, questionCount);
                    });
                }
                
                // Answer character counts
                for (let i = 1; i <= 6; i++) {
                    const answerField = document.querySelector(`textarea[name="Question[a${i}]"]`);
                    const answerCount = document.getElementById(`answer-${i}-count`);
                    
                    if (answerField && answerCount) {
                        updateCharacterCount(answerField, answerCount);
                        answerField.addEventListener('input', function() {
                            updateCharacterCount(this, answerCount);
                        });
                    }
                }
                
                // Correct answer highlighting
                const correctField = document.querySelector('input[name="Question[correct]"]');
                if (correctField) {
                    correctField.addEventListener('input', function() {
                        updateCorrectAnswerHighlighting();
                    });
                    updateCorrectAnswerHighlighting();
                }
            });
            
            function updateCharacterCount(field, countElement) {
                const length = field.value.length;
                countElement.textContent = `${length} characters`;
                
                // Add warning classes based on length
                countElement.className = 'character-count';
                if (length > 500) {
                    countElement.classList.add('warning');
                }
                if (length > 800) {
                    countElement.classList.add('danger');
                }
            }
            
            function updateCorrectAnswerHighlighting() {
                const correctValue = document.querySelector('input[name="Question[correct]"]').value;
                const answerFields = document.querySelectorAll('.answer-field');
                
                answerFields.forEach((field, index) => {
                    const answerNumber = index + 1;
                    if (correctValue == answerNumber) {
                        field.classList.add('correct-answer');
                    } else {
                        field.classList.remove('correct-answer');
                    }
                });
            }
        </script>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?= Html::submitButton('💾 Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?php
                if ( Yii::$app->user->returnUrl ) {
                    $returnUrl = Yii::$app->user->returnUrl;
                } else {
                    $returnUrl = Yii::$app->request->referrer;
                }
                echo Html::a('⬅️ Back', $returnUrl, ['class' => 'btn btn-primary quiz-button']);

                $url = Yii::$app->urlManager->createUrl(['/question/copy', 'id' => $model['id'], 'quiz_id' => $quiz_id ?? null]);
                echo Html::a('📋 Copy', $url, [
                    'title' => 'Copy Question' . ($quiz_id ? ' and add to current quiz' : ''),
                    'class' => 'btn btn-warning quiz-button',
                ]);

                $url = Yii::$app->urlManager->createUrl(['/question/alternative', 'question_id' => $model['id']]);
                echo Html::a('🤖 Alternative (AI)', $url, [
                    'title' => 'Generate Alternative Question using AI',
                    'class' => 'btn btn-danger quiz-button',
                ]);
            ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>