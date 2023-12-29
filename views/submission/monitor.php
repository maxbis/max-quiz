<?php
// views/your-controller/submissions.php

use yii\helpers\Html;

/* @var $submissions array */

?>


<h1>Submissions</h1>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Class</th>
            <th>#Questions</th>
            <th>Answered</th>
            <th>Correct</th>
            <th>Progress (%)</th>
            <th>Score (%)</th>
            <th>Last_updated</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($submissions as $submission): ?>
            <tr>
                <td><?= Html::encode($submission['first_name']) ?> <?= Html::encode($submission['last_name']) ?></td>
                <td><?= Html::encode($submission['class']) ?></td>
                <td><?= Html::encode($submission['no_questions']) ?></td>
                <td><?= Html::encode($submission['no_answered']) ?></td>
                <td><?= Html::encode($submission['no_correct']) ?></td>
                <td>
                    <?= $submission['no_answered'] == 0 ? 0 : round($submission['no_answered'] / $submission['no_questions'] * 100, 0) ?>%
                </td>
                <td>
                    <?= $submission['no_answered'] == 0 ? 0 : round($submission['no_correct'] / $submission['no_answered'] * 100, 0) ?>%
                </td>
                <td><?= Html::encode($submission['last_updated']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
