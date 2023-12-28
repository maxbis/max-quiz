<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Question $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Questions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="question-view">

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
                    if ( $model->correct == 1 ) {
                        return Html::tag('strong', Html::encode($model->a1),['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a1);
                    }
                },
            ],
            [
                'attribute' => 'a2',
                'format' => 'raw', 
                'value' => function ($model) {
                    if ( $model->correct == 2 ) {
                        return Html::tag('strong', Html::encode($model->a2),['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a2);
                    }
                },
            ],
            [
                'attribute' => 'a3',
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 3 ) {
                        return Html::tag('strong', Html::encode($model->a3),['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a3);
                    }
                },
            ],
            [
                'attribute' => 'a4',
                'format' => 'raw', 
                'value' => function ($model) {
                    if ( $model->correct == 4 ) {
                        return Html::tag('strong', Html::encode($model->a4),['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a4);
                    }
                },
            ],
            [
                'attribute' => 'a5',
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 5 ) {
                        return Html::tag('strong', Html::encode($model->a5),['style' => 'background-color: red;']);
                    } else {
                        return Html::encode($model->a5);
                    }
                },
            ],
            [
                'attribute' => 'a6',
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 6 ) {
                        return Html::tag('strong', Html::encode($model->a6),['style' => 'background-color: red;']);
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
            <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        </span>
        <span style="margin:20px;">
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </span>
        <span style="margin:20px;">
            <?= Html::a('Copy', ['copy', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        </span>
    </p>

</div>
