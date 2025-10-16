<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Question $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
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


    <div class="card" style="width: 60rem;padding:30px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'question')->textarea([
                    'rows' => 10,
                    'style' => 'font-family: monospace;', // Inline CSS for monospace font
                    'maxlength' => true
                ]) ?>
            </div>
        </div>


        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'a1')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace; ',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a2')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'a3')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a4')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'a5')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a6')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'correct')->textInput([
                    'style' => 'width: 160px;',
                    'maxlength' => true
                ]) ?>
            </div>

            <?php if (isset($quiz_id) && $quiz_id): ?>
            <div class="col">
                <div class="form-group">
                    <label class="control-label" for="quiz_order">Sort Order</label>
                    <?= Html::textInput('quiz_order', $currentOrder, [
                        'class' => 'form-control',
                        'style' => 'width: 120px;',
                        'type' => 'number',
                        'min' => '0',
                        'placeholder' => '0',
                        'title' => 'Order of this question in the quiz (lower numbers appear first)'
                    ]) ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="col">
                <?= $form->field($model, 'label')->textInput([
                    'rows' => 2,
                    'maxlength' => true
                ]) ?>
            </div>
        
        </div>

        <hr>

        <?php if (isset($questionLinks) && $questionLinks != '' ) { ?>
            <div class="row justify-content-start">
                <div class="col-12">
                    <h6>Quiz Assignments: 
                        <span class="badge bg-info" id="assignmentCount">
                            <?= count(array_filter($questionLinks, function($link) { return $link['active']; })) ?> of <?= count($questionLinks) ?> selected
                        </span>
                    </h6>
                    
                    <!-- Collapsible Quiz List -->
                    <div class="mb-3">
                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#quizAssignments" aria-expanded="false">
                            📋 Manage Quiz Assignments
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm ms-2" onclick="toggleAllQuizzes(true)">✓ Select All</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="toggleAllQuizzes(false)">✗ Deselect All</button>
                        
                        <?php 
                        $showArchived = $show_archived ?? 0;
                        $currentUrl = Yii::$app->request->url;
                        $baseUrl = strtok($currentUrl, '?');
                        $params = Yii::$app->request->queryParams;
                        ?>
                        
                        <?php if ($showArchived): ?>
                            <?= Html::a('📦 Hide Archived', array_merge(['question/update'], ['id' => $model->id], array_diff_key($params, ['show_archived' => '']), ['show_archived' => 0]), [
                                'class' => 'btn btn-outline-secondary btn-sm ms-2',
                                'title' => 'Show only active quizzes'
                            ]) ?>
                        <?php else: ?>
                            <?= Html::a('📂 Show Archived', array_merge(['question/update'], ['id' => $model->id], $params, ['show_archived' => 1]), [
                                'class' => 'btn btn-outline-info btn-sm ms-2',
                                'title' => 'Show all quizzes including archived'
                            ]) ?>
                        <?php endif; ?>
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
        

        <hr>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?php
                if ( Yii::$app->user->returnUrl ) {
                    $returnUrl = Yii::$app->user->returnUrl;
                } else {
                    $returnUrl = Yii::$app->request->referrer;
                }
                echo Html::a('Back', $returnUrl, ['class' => 'btn btn-primary quiz-button']);

                $url = Yii::$app->urlManager->createUrl(['/question/copy', 'id' => $model['id']]);
                echo Html::a('Copy', $url, [
                    'title' => 'Copy Question',
                    'class' => 'btn btn-warning quiz-button',
                ]);

                $url = Yii::$app->urlManager->createUrl(['/question/alternative', 'question_id' => $model['id']]);
                echo Html::a('Alternative (AI)', $url, [
                    'title' => 'Alternative Question (AI)',
                    'class' => 'btn btn-danger quiz-button',
                ]);
            ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>