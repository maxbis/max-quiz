<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Question $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Questions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

// Get return URL from parameter
$returnUrl = Yii::$app->request->get('returnUrl', 'index');
$quiz_id = Yii::$app->request->get('quiz_id');

// Determine the back URL based on returnUrl parameter
if ($returnUrl === 'edit-labels' && $quiz_id) {
    $backUrl = ['quiz/edit-labels', 'id' => $quiz_id];
    $backLabel = '← Back to Edit Labels';
} else {
    $backUrl = ['index', 'id' => $quiz_id];
    $backLabel = '← Back to Question Index';
}
?>

<style>
    .answer {
        padding: 6px;
        border: 1px solid #ddd;
        margin: 40px;
        cursor: pointer;
        text-align: left;
        min-height: 4em;
        font-family: monospace;
        user-select: none;
    }

    .selected {
        background-color: #007bff;
        color: white;
    }

    .question-block {
        font-family: monospace;
        /* Monospaced font */
        background-color: #f8f8f8;
        /* Paper-like background color */
        border: 1px solid #ddd;
        /* Optional: adds a subtle border */
        padding: 15px;
        /* Padding around the text */
        min-height: 9em;
        /* Minimum height for about five lines of text */
        text-align: left;
        /* Align text to the left */
        user-select: none;
        overflow-x: hidden;
    }

    .question-title {
        margin-top: 80px;
        font-size: larger;
        /* Makes the font larger */
        text-align: left;
        /* Aligns text to the left */
    }

    pre {
        margin-left: 40px;
        font-size: 16px;
        font-family: monospace;
        color: darkblue;
    }
</style>

<div class="question-view">

    <p style="margin-bottom: 20px;">
        <?= Html::a($backLabel, $backUrl, [
            'class' => 'btn btn-secondary',
            'style' => 'font-size: 14px;'
        ]) ?>
    </p>

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'question',
                'format' => 'raw',
                'value' => function ($model) {
                    // return nl2br(Html::encode($model->question));
                    // return nl2br( Html::tag('pre', Html::encode($model->question), ['style' => 'font-family: monospace;']) );
                    return Html::tag('div', Html::encode($model->question), [
                        'style' => 'font-family: monospace; overflow: hidden; text-overflow:
                                    ellipsis; line-height: 1.2em; height: 12em; white-space: pre-wrap;'
                    ]);
                },
            ],
            [
                'attribute' => 'a1',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->correct == 1) {
                        return Html::tag('strong', Html::encode($model->a1), ['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a1);
                    }
                },
            ],
            [
                'attribute' => 'a2',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->correct == 2) {
                        return Html::tag('strong', Html::encode($model->a2), ['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a2);
                    }
                },
            ],
            [
                'attribute' => 'a3',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->correct == 3) {
                        return Html::tag('strong', Html::encode($model->a3), ['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a3);
                    }
                },
            ],
            [
                'attribute' => 'a4',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->correct == 4) {
                        return Html::tag('strong', Html::encode($model->a4), ['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a4);
                    }
                },
            ],
            [
                'attribute' => 'a5',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->correct == 5) {
                        return Html::tag('strong', Html::encode($model->a5), ['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a5);
                    }
                },
            ],
            [
                'attribute' => 'a6',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->correct == 6) {
                        return Html::tag('strong', Html::encode($model->a6), ['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a6);
                    }
                },
            ],
            'correct',
            'label',
        ],
         'options' => [
            'class' => 'table table-striped table-bordered detail-view',
            'style' => 'width: 600px;', // Inline CSS to set the width
        ],
    ]) ?>


    <p style="margin-top:60px;">
        <span style="margin:20px;">
            <?= Html::a('Edit', ['update', 'id' => $model->id, 'quiz_id' => $quiz_id], ['class' => 'btn btn-success']) ?>
        </span>
        <span style="margin:20px;">
            <?= Html::a('Delete', ['delete', 'id' => $model->id, 'quiz_id' => $quiz_id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </span>
        <span style="margin:20px;">
            <?= Html::a('Copy', ['copy', 'id' => $model->id, 'quiz_id' => $quiz_id], ['class' => 'btn btn-warning']) ?>
        </span>
        <span style="margin:20px;">
            <?= Html::a($backLabel, $backUrl, ['class' => 'btn btn-secondary']) ?>
        </span>
    </p>

</div>


<div class="container-fluid banner-container text-white text-center py-3">
    <div class="banner-content">
        <h1>Titel</h1>
        <p>vraag n</p>
    </div>
</div>

<div class="container text-center">
    <div class="row justify-content-center page-effect">
        <div class="col-12 question-title">Vraag n</div>
        <div class="col-12">
            <div class="question-block">
                <?= $model->question ?>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Answers Column 1 -->
            <?php if (  isset($model->a1) ) { ?>
                <div class="answer" onclick="selectAnswer(this, '<?= $model->a1 ?>')"><?= $model->a1 ?></div>
            <?php } ?>
            <?php if (  isset($model->a3) ) { ?>
                <div class="answer" onclick="selectAnswer(this, '<?= $model->a3 ?>')"><?= $model->a3 ?></div>
            <?php } ?>
            <?php if (  isset($model->a5) ) { ?>
                <div class="answer" onclick="selectAnswer(this, '<?= $model->a5 ?>')"><?= $model->a5 ?></div>
            <?php } ?>
        </div>

        <div class="col-md-6">
            <!-- Answers Column 2 -->
            <?php if (  isset($model->a2) ) { ?>
                <div class="answer" onclick="selectAnswer(this, '<?= $model->a2 ?>')"><?= $model->a2 ?></div>
            <?php } ?>
            <?php if (  isset($model->a4) ) { ?>
                <div class="answer" onclick="selectAnswer(this, '<?= $model->a4 ?>')"><?= $model->a4 ?></div>
            <?php } ?>
            <?php if (  isset($model->a6) ) { ?>
                <div class="answer" onclick="selectAnswer(this, '<?= $model->a6 ?>')"><?= $model->a6 ?></div>
            <?php } ?>
        </div>
    </div>
</div>