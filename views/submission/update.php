<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Submission $model */

$this->title = 'Update Submission: ' . $model->id;

?>
<div class="submission-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
