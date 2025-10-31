<?php

use yii\helpers\Html;

/** @var array $backups */
/** @var int|string|null $quizId */

$this->title = 'Submission Backups';
?>

<div class="maintenance-backups">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!empty($quizId)): ?>
        <p>Filtered by quiz ID: <strong><?= Html::encode($quizId) ?></strong></p>
    <?php endif; ?>

    <?php if (empty($backups)): ?>
        <p>No backups found.</p>
    <?php else: ?>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Batch Key</th>
                    <th>Quiz ID</th>
                    <th>Question ID</th>
                    <th>Created</th>
                    <th>Submissions</th>
                    <th>Note</th>
                    <th>Restore</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><?= Html::encode($backup['batch_key']) ?></td>
                        <td><?= Html::encode($backup['quiz_id']) ?></td>
                        <td><?= Html::encode($backup['question_id']) ?></td>
                        <td><?= Html::encode($backup['created_at']) ?></td>
                        <td><?= Html::encode($backup['submission_count']) ?></td>
                        <td><?= Html::encode($backup['note']) ?></td>
                        <td>
                            <?= Html::a('Restore', ['maintenance/restore', 'batch_key' => $backup['batch_key']], [
                                'class' => 'btn btn-outline-danger btn-sm',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Restore this batch? Current data will be overwritten.',
                                ],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

