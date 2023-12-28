<?php

use app\models\Question;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\QuestionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Questions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="question-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Question', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            [
                'attribute' => 'id',
                'label' => 'id',
            ],
            'question',
            [
                'attribute' => 'a1',
                'label' => 'a1',
                'contentOptions' => ['style' => 'width:80px; white-space: normal;'],
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 1 ) {
                        return Html::tag('strong', Html::encode($model->a1));
                    } else {
                        return Html::encode($model->a1);
                    }
                },
            ],
            [
                'attribute' => 'a2',
                'label' => 'a2',
                'format' => 'raw', 
                'value' => function ($model) {
                    if ( $model->correct == 2 ) {
                        return Html::tag('strong', Html::encode($model->a2));
                    } else {
                        return Html::encode($model->a2);
                    }
                },
            ],
            [
                'attribute' => 'a3',
                'label' => 'a3',
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 3 ) {
                        return Html::tag('strong', Html::encode($model->a3));
                    } else {
                        return Html::encode($model->a3);
                    }
                },
            ],
            [
                'attribute' => 'a4',
                'label' => 'a4',
                'format' => 'raw', 
                'value' => function ($model) {
                    if ( $model->correct == 4 ) {
                        return Html::tag('strong', Html::encode($model->a4));
                    } else {
                        return Html::encode($model->a4);
                    }
                },
            ],
            [
                'attribute' => 'a5',
                'label' => 'a5',
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 5 ) {
                        return Html::tag('strong', Html::encode($model->a5));
                    } else {
                        return Html::encode($model->a5);
                    }
                },
            ],
            [
                'attribute' => 'a6',
                'label' => 'a6',
                'format' => 'raw',
                'value' => function ($model) {
                    if ( $model->correct == 6 ) {
                        return Html::tag('strong', Html::encode($model->a6));
                    } else {
                        return Html::encode($model->a6);
                    }
                },
            ],
            [
                'attribute' => 'correct',
                'label' => 'Correct'
            ],
            'label',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Question $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
