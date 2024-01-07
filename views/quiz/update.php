<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Quiz $model */

$this->title = 'Update Quiz ' . $model->name;

?>
<p style='color:#909090;font-size:16px;'><?=$this->title?></p>
<div class="quiz-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
