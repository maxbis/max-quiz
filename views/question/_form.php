<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Question $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<div class="question-form">

    <?php $form = ActiveForm::begin(); ?>


    <div class="card" style="width: 60rem;padding:30px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'question')->textarea([
                    'rows' => 10,
                    'style' => 'font-family: monospace;', // Inline CSS for monospace font
                    'maxlength' => true
                ]) ?>
            </div>
        </div>


        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'a1')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace; ',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a2')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'a3')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a4')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'a5')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a6')->textarea([
                    'rows' => 2,
                    'style' => 'font-family: monospace;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model, 'correct')->textInput([
                    'style' => 'width: 160px;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'label')->textInput([
                    'rows' => 2,
                    'maxlength' => true
                ]) ?>
            </div>
        </div>
        
        <hr>
        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?= Html::a('Cancel', Yii::$app->request->referrer, ['class' => 'btn btn-primary quiz-button']); ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>