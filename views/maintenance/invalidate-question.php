<?php

use yii\helpers\Html;

/** @var app\models\Quiz $quiz */
/** @var app\models\Question $question */
/** @var int $affectedCount */
/** @var int $answeredCount */
/** @var array|null $result */

$this->title = 'Invalidate Question';
?>

<div class="maintenance-invalidate">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        Quiz: <strong><?= Html::encode($quiz->name) ?> (ID <?= Html::encode($quiz->id) ?>)</strong><br>
        Question: <strong>#<?= Html::encode($question->id) ?></strong>
    </p>

    <p>
        Submissions containing this question: <strong><?= Html::encode($affectedCount) ?></strong><br>
        Of which answered: <strong><?= Html::encode($answeredCount) ?></strong>
    </p>

    <?php if ($result): ?>
        <div class="alert alert-success">
            <p>Updated submissions: <?= Html::encode($result['updated']) ?></p>
            <p>Removed log rows: <?= Html::encode($result['logsRemoved']) ?></p>
            <p>Batch key: <code><?= Html::encode($result['batchKey']) ?></code></p>
        </div>
    <?php endif; ?>

    <p class="text-danger"><strong>Warning:</strong> This action cannot be undone without using the backup restore route. A backup will be created automatically before changes are applied.</p>

    <?= Html::beginForm(['maintenance/invalidate-question', 'quiz_id' => $quiz->id, 'question_id' => $question->id], 'post', [
        'class' => 'mb-3',
    ]) ?>
        <?= Html::submitButton('Invalidate Question', [
            'class' => 'btn btn-danger',
            'data-confirm' => 'Are you sure you want to invalidate this question for all submissions in this quiz?',
        ]) ?>
    <?= Html::endForm() ?>

    <p><?= Html::a('View backups', ['maintenance/backups', 'quiz_id' => $quiz->id]) ?></p>
</div>

