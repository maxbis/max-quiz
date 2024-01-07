<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Question $model */

$this->title = 'Update Question: ' . $model->id;

?>
<div class="question-update">

    <p style='color:#909090;font-size:16px;'><?= $this->title ?></p>

    <?= $this->render('_form', [
        'model' => $model,
        'questionLinks' => $questionLinks,
    ]) ?>

</div>