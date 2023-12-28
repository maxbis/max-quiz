<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Submission $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="submission-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'token')->textInput() ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'class')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'start_time')->textInput() ?>

    <?= $form->field($model, 'end_time')->textInput() ?>

    <?= $form->field($model, 'question_order')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_questions')->textInput() ?>

    <?= $form->field($model, 'no_answered')->textInput() ?>

    <?= $form->field($model, 'no_correct')->textInput() ?>

    <?= $form->field($model, 'quiz_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
