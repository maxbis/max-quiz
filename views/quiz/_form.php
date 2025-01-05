<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Quiz $model */
/** @var yii\widgets\ActiveForm $form */

$id = Yii::$app->request->get('id');
?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<br>

<div class="quiz-card"
    style="max-width:600px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
    <div class="quiz-form">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-6">

                <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Quiz Name') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'password')->textInput(['maxlength' => true])->label('Unique code to access quiz') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'active')->dropDownList(
                    [1 => 'Yes', 0 => 'No'], // Options: value => display text
                    ['prompt' => '...', 'style' => 'width: 200px;'] // Optional: prompt message
                )->label('Active') ?>
            </div>
            <div class="col-md-6">

                <?= $form->field($model, 'no_questions')->textInput(['style' => 'width: 200px;'])->label('Max number of questions') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'review')->dropDownList(
                    [1 => 'Review possible', 0 => 'No Review'], // Options: value => display text
                    ['prompt' => '...', 'style' => 'width: 150px;'] // Optional: prompt message
                )->label('Review Quiz') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'blind')->dropDownList(
                    [0 => 'On Screen', 1 => 'On Paper'], // Options: value => display text
                    ['prompt' => '...', 'style' => 'width: 150px;'] // Optional: prompt message
                )->label('Blind quiz') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'ip_check')->dropDownList(
                    [0 => 'Everyone Allowed', 1 => 'IP Restricted'], // Options: value => display text
                    ['prompt' => '...', 'style' => 'width: 150px;'] // Optional: prompt message
                )->label('IP Check') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'random')->dropDownList(
                    [0 => 'Questions in right order', 1 => 'Random Order'], // Options: value => display text
                    ['prompt' => '...', 'style' => 'width: 150px;'] // Optional: prompt message
                )->label('Sequential (label,id)') ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?= Html::a('Cancel', Yii::$app->request->referrer, ['class' => 'btn btn-primary quiz-button']); ?>
        </div>

        <?php ActiveForm::end(); ?>


    </div>

</div>