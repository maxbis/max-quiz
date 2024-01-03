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

<div class="quiz-card" style="max-width:600px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
    <div class="quiz-form" style="width:400px;">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Quiz Name') ?>

        <?= $form->field($model, 'password')->textInput(['maxlength' => true])->label('Unique code to access quiz') ?>

        <?= $form->field($model, 'active')->dropDownList(
            [1 => 'Active', 0 => 'Not Active'], // Options: value => display text
            ['prompt' => 'Select Status', 'style' => 'width: 200px;'] // Optional: prompt message
        )->label('Status') ?>


        <?= $form->field($model, 'no_questions')->textInput(['style' => 'width: 200px;'])->label('Max number of questions') ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?= Html::a( 'Cancel', Yii::$app->request->referrer , ['class'=>'btn btn-primary quiz-button']); ?>
        </div>

        <?php ActiveForm::end(); ?>


    </div>

</div>