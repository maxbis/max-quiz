<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<style>
    .container {
        display: flex;
    }

    .col {
        margin-left: 0px;
    }

    .question-title {
        margin-bottom: 5px;
        text-align: left;
        color: darkblue;
    }

    .quiz-button {
        font-size: 12px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<body>
    <div class="question-form">
        <?php $form = ActiveForm::begin();
        $teller = 0; ?>

        <?php foreach ($models as $index => $model) : ?>
            <div class="question-title">Question <?= ++$teller ?></div>
            <div class="card" style="padding:30px;margin-bottom:40px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
                <div class="row">
                    <div class="col">
                        <div class="">
                            <?= $form->field($model, "[$index]question")->textarea(['rows' => 10, 'style' => 'font-family: monospace;width:600px;', 'maxlength' => true]) ?>
                        </div>
                        <div class="row justify-content-start">
                        </div>
                    </div>
                    <div class="col" style="margin-left:30px;">
                        <?php for ($i = 1; $i <= 6; $i += 2) {  ?>
                            <div class="row justify-content-start">
                                <div class="col">
                                    <?= $form->field($model, "[$index]a" . ($i))->textarea([
                                        'rows' => 2,
                                        'style' => 'font-family: monospace; ',
                                    ]) ?>
                                </div>
                                <div class="col">
                                    <?= $form->field($model, "[$index]a" . ($i + 1))->textarea([
                                        'rows' => 2,
                                        'style' => 'font-family: monospace;',
                                    ]) ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row ustify-content-between">
                    <div class="col">
                        <?= $form->field($model, 'label')->textInput([
                            'rows' => 2,
                            'style' => 'width: 450px;',
                            'maxlength' => true
                        ]) ?>
                    </div>
                    <div class="col">
                        <?= $form->field($model, 'correct')->textInput([
                            'style' => 'width: 60px;',
                        ])->label('Correct') ?>
                    </div>
                    <?php
                        $b1 = Html::a(
                            'View/copy',
                            ['view', 'id' => $model['id']],
                            ['class' => 'btn btn-outline-warning quiz-button', 'title' => 'View/Copy']
                        );
                    ?>
                    <div class="col" style="display: flex; justify-content: flex-end; align-items: flex-end;"><?= $b1 ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?= Html::a('Cancel', Yii::$app->request->referrer, ['class' => 'btn btn-primary quiz-button']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</body>