<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Question $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="question-form">

    <?php $form = ActiveForm::begin(); ?>


    <div class="container">

        <div class="row">
            <div class="col">
                <?= $form->field($model, 'question')->textarea([
                    'rows' => 8,
                    'style' => 'font-family: monospace; width: 800px;', // Inline CSS for monospace font
                    'maxlength' => true
                ]) ?>
            </div>
        </div>


        <div class="row">
            <div class="col">
                <?= $form->field($model, 'a1')->textarea([
                    'rows' => 3,
                    'style' => 'font-family: monospace; width: 600px;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a2')->textarea([
                    'rows' => 3,
                    'style' => 'font-family: monospace; width: 600px;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <?= $form->field($model, 'a3')->textarea([
                    'rows' => 3,
                    'style' => 'font-family: monospace; width: 600px;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a4')->textarea([
                    'rows' => 3,
                    'style' => 'font-family: monospace; width: 600px;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <?= $form->field($model, 'a5')->textarea([
                    'rows' => 3,
                    'style' => 'font-family: monospace; width: 600px;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'a6')->textarea([
                    'rows' => 3,
                    'style' => 'font-family: monospace; width: 600px;',
                    'maxlength' => true
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <?= $form->field($model, 'correct')->textInput([
                    'style' => 'width: 200px;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'label')->textInput([
                    'rows' => 3,
                    'style' => 'width: 200px;',
                    'maxlength' => true
                ]) ?>
            </div>
            <div class="col">

            </div>
        </div>

    </div>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
