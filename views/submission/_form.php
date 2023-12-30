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

<div class="submission-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'token')->textInput(['readonly' => true]) ?>
            <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'class')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'start_time')->textInput() ?>
            <?= $form->field($model, 'end_time')->textInput() ?>
        </div>
        <div class="col-md-4">
            
            <?= $form->field($model, 'question_order')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'no_questions')->textInput() ?>
            <?= $form->field($model, 'no_answered')->textInput() ?>
            <?= $form->field($model, 'no_correct')->textInput() ?>
            <?= $form->field($model, 'finished')->textInput() ?>
            <?= $form->field($model, 'quiz_id')->textInput() ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <hr>

    <?= Html::a('Open this quiz', ['submission/restart', 'token' => $model->token], ['class' => 'btn btn-primary']); ?>
    <?php
        echo Html::button('Copy Link to this quiz', ['class' => 'btn btn-secondary', 'id' => 'copy-button']);
        $urlToCopy = Url::to(['submission/restart', 'token' => $model->token], true);
        echo Html::textInput('hidden-url', $urlToCopy, ['id' => 'hidden-url', 'readonly' => true, 'style' => 'position: absolute; left: -9999px;']);
    ?>

</div>
