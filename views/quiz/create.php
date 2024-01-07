<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Quiz $model */

$this->title = 'Create Quiz';
?>
<div class="quiz-create">
    <p style='color:#909090;font-size:16px;'><?= $this->title ?></p>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>