<?php

use yii\helpers\Html;

/** @var string $batchKey */
/** @var array $rows */
/** @var array|null $result */

$this->title = 'Restore Backup';
?>

<div class="maintenance-restore">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Batch key: <strong><?= Html::encode($batchKey) ?></strong></p>
    <p>Backed up submissions: <strong><?= Html::encode(count($rows)) ?></strong></p>

    <?php if ($result): ?>
        <div class="alert alert-success">
            <p>Restored submissions: <?= Html::encode($result['submissions']) ?></p>
            <p>Restored log entries: <?= Html::encode($result['logs']) ?></p>
        </div>
    <?php endif; ?>

    <p class="text-warning">
        Restoring will overwrite the current state of the affected submissions and their log entries.
    </p>

    <?= Html::beginForm(['maintenance/restore', 'batch_key' => $batchKey], 'post') ?>
        <?= Html::submitButton('Restore this backup', [
            'class' => 'btn btn-danger',
            'data-confirm' => 'Are you sure you want to overwrite current submissions with this backup?',
        ]) ?>
    <?= Html::endForm() ?>

    <p><?= Html::a('Back to backups list', ['maintenance/backups']) ?></p>
</div>

