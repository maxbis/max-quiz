<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Question $model */

$this->title = 'Update Question: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Questions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="question-update">

    <h4><?= Html::encode($this->title) ?></h4>

    <?= $this->render('_form', [
        'model' => $model,
        'questionLinks' => $questionLinks,
    ]) ?>

</div>
