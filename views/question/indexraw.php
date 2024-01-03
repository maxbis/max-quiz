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

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn'
            ],
            [
                'attribute' => 'id',
                'label' => 'id',
                'headerOptions' => ['style' => 'width:50px;'],
            ],
            [
                'attribute' => 'question',
                'value' => function ($model) {
                    return mb_substr($model->question, 0, 100) . (mb_strlen($model->question) > 100 ? '...' : '');
                },
            ],
            [
                'attribute' => 'correct',
                'label' => 'correct',
                'headerOptions' => ['style' => 'width:100px;'],
            ],
            [
                'attribute' => 'label',
                'label' => 'Label',
                'headerOptions' => ['style' => 'width:200px;'],
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Question $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>