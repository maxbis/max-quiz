<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<style>
    .container {
        display: flex;
    }

    .row {
        margin-left: 20px;
    }

    .question-title {
        margin-top: 0px;
        margin-bottom: 20px;
        text-align: left;
        color: darkblue;
    }
</style>

<body>
    <div class="question-form">
        <?php $form = ActiveForm::begin();
        $teller = 0; ?>

        <?php foreach ($models as $index => $model) : ?>
            <span class="question-title">Question <?= ++$teller ?></span>
            <div class="card" style="padding:30px;margin-bottom:40px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
                <div class="container">
                    <div class="row">
                        <?= $form->field($model, "[$index]question")->textarea(['rows' => 10, 'style' => 'font-family: monospace;width:600px;', 'maxlength' => true]) ?>
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
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
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
            </div>
        <?php endforeach; ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?= Html::a('Cancel', Yii::$app->request->referrer, ['class' => 'btn btn-primary quiz-button']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</body>