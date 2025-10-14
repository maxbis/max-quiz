<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Question $model */

$this->title = 'Create Question';
?>
<div class="question-create">

    <p style='color:#909090;font-size:16px;'><?= $this->title ?></p>
    
    <?= $this->render('_form', [
        'model' => $model,
        'questionLinks' => $questionLinks ?? null,
        'quiz_id' => $quiz_id ?? null,
        'currentOrder' => $currentOrder ?? null,
    ]) ?>

</div>