<?php

use yii\helpers\Html;

/** @var array $quizOptions */
/** @var app\models\Quiz|null $selectedQuiz */
/** @var array $questionOptions */
/** @var int|string|null $selectedQuizId */

$this->title = 'Maintenance Tools';
?>

<div class="maintenance-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-warning">
        <strong>Important:</strong> These tools can permanently alter submission data. Always double-check your selections and ensure backups exist before proceeding.
    </div>

    <div class="card mb-4">
        <div class="card-header">1. Choose quiz</div>
        <div class="card-body">
            <p>Select a quiz to view question invalidation options and related backups.</p>
            <?= Html::beginForm(['maintenance/index'], 'get', ['class' => 'row gy-2 gx-2 align-items-center']) ?>
                <div class="col-sm-6 col-md-4">
                    <?= Html::dropDownList('quiz_id', $selectedQuizId, $quizOptions, [
                        'class' => 'form-select',
                        'prompt' => 'Select a quiz…',
                        'onchange' => 'this.form.submit();',
                    ]) ?>
                </div>
                <div class="col-auto">
                    <?= Html::submitButton('Load', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>

            <div class="mt-3">
                <?= Html::a('View all backups', ['maintenance/backups'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                <?php if ($selectedQuiz): ?>
                    <?= Html::a('Backups for this quiz', ['maintenance/backups', 'quiz_id' => $selectedQuiz->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($selectedQuiz): ?>
        <div class="card mb-4">
            <div class="card-header">2. Invalidate a question</div>
            <div class="card-body">
                <?php if (empty($questionOptions)): ?>
                    <p class="text-muted">No active questions found for this quiz.</p>
                <?php else: ?>
                    <p>Select a question to open the invalidate screen. A backup will be created automatically before any changes are applied.</p>
                    <?= Html::beginForm(['maintenance/invalidate-question'], 'get', ['class' => 'row gy-2 gx-2 align-items-center']) ?>
                        <?= Html::hiddenInput('quiz_id', $selectedQuiz->id) ?>
                        <div class="col-sm-7 col-md-5">
                            <?= Html::dropDownList('question_id', null, $questionOptions, [
                                'class' => 'form-select',
                                'prompt' => 'Select a question…',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-auto">
                            <?= Html::submitButton('Open invalidate screen', ['class' => 'btn btn-danger']) ?>
                        </div>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">3. Restore from backup</div>
        <div class="card-body">
            <p>Know a batch key already? Paste it below to jump to the restore screen.</p>
            <?= Html::beginForm(['maintenance/restore'], 'get', ['class' => 'row gy-2 gx-2 align-items-center']) ?>
                <div class="col-sm-6 col-md-4">
                    <?= Html::textInput('batch_key', '', [
                        'class' => 'form-control',
                        'placeholder' => 'Enter batch key…',
                    ]) ?>
                </div>
                <div class="col-auto">
                    <?= Html::submitButton('Open restore page', ['class' => 'btn btn-outline-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

