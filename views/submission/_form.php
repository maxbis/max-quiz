<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use yii\helpers\Url;

$script = <<< JS
document.getElementById("copy-button").onclick = function() {
    var copyText = document.getElementById("hidden-url");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");
    alert("Copied the link: " + copyText.value);
}
JS;
$this->registerJs($script);

?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<div class="quiz-card" style="max-width:600px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
    <div class="quiz-form">
        <h4>Update submission for <?= $model->quiz['name'] ?></h4>
        <p></p>
        <hr>
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col">
                <?= $form->field($model, 'start_time')->textInput(['readonly' => true, 'style' => 'background-color: #f0f0f0;']) ?>
                <?= $form->field($model, 'quiz_id')->textInput(['readonly' => true, 'style' => 'background-color: #f0f0f0;']) ?>
                <?= $form->field($model, 'no_questions')->textInput(['readonly' => true, 'style' => 'background-color: #f0f0f0;']) ?>
                <?= $form->field($model, 'no_answered')->textInput() ?>
                <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'style' => 'background-color: #FFFFE0;']) ?>
                <?= $form->field($model, 'class')->textInput(['maxlength' => true]) ?>


            </div>
            <div class="col">
                <?= $form->field($model, 'end_time')->textInput(['readonly' => true, 'style' => 'background-color: #f0f0f0;']) ?>
                <?= $form->field($model, 'token')->textInput(['readonly' => true, 'style' => 'background-color: #f0f0f0;']) ?>
                <?= $form->field($model, 'question_order')->textInput(['readonly' => true, 'maxlength' => true, 'style' => 'background-color: #f0f0f0;']) ?>
                <?= $form->field($model, 'no_correct')->textInput() ?>
                <?= $form->field($model, 'last_name')->textInput(['maxlength' => true, 'style' => 'background-color: #FFFFE0;']) ?>

                <?= $form->field($model, 'finished')->dropDownList(
                    [1 => 'Finshed', 0 => 'In Progress'], // Options: value => display text
                    ['prompt' => 'Select Status', 'style' => 'width: 200px;'] // Optional: prompt message
                )->label('Status') ?>

            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?php
            $url = Url::to(['/submission/index', 'quiz_id' => $model->quiz['id']]);
            echo Html::a('Back', [$url], ['class' => 'btn btn-primary quiz-button']);
            echo Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger quiz-button',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>

        <hr>

        <?= Html::a('Open quiz', ['/submission/restart', 'token' => $model->token], ['class' => 'btn btn-outline-primary quiz-button']); ?>
        <?php
        echo Html::button('Link to quiz', ['class' => 'btn btn-outline-secondary quiz-button', 'id' => 'copy-button']);
        $urlToCopy = Url::to(['/submission/restart', 'token' => $model->token], true);
        echo Html::textInput('hidden-url', $urlToCopy, ['id' => 'hidden-url', 'readonly' => true, 'style' => 'position: absolute; left: -9999px;']);
        ?>
        <?= Html::a('Results', ['/site/results', 'token' => $model->token], ['class' => 'btn btn-outline-warning quiz-button']); ?>

    </div>
</div>