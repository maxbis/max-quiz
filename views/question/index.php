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
            [
                'attribute' => 'question',
                'value' => function ($model) {
                    return mb_substr($model->question, 0, 40) . (mb_strlen($model->question) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->question];
                },
            ],
            [
                'attribute' => 'a1',
                'label' => '1',
                'value' => function ($model) {
                    return mb_substr($model->a1, 0, 20) . (mb_strlen($model->a1) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->a1];
                },
            ],
            [
                'attribute' => 'a2',
                'label' => '2',
                'value' => function ($model) {
                    return mb_substr($model->a2, 0, 20) . (mb_strlen($model->a2) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->a2];
                },
            ],
            [
                'attribute' => 'a3',
                'label' => '3',
                'value' => function ($model) {
                    return mb_substr($model->a3, 0, 20) . (mb_strlen($model->a3) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->a3];
                },
            ],
            [
                'attribute' => 'a4',
                'label' => '4',
                'value' => function ($model) {
                    return mb_substr($model->a4, 0, 20) . (mb_strlen($model->a4) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->a4];
                },
            ],
            [
                'attribute' => 'a5',
                'label' => '5',
                'value' => function ($model) {
                    return mb_substr($model->a5, 0, 20) . (mb_strlen($model->a5) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->a5];
                },
            ],
            [
                'attribute' => 'a6',
                'label' => '6',
                'value' => function ($model) {
                    return mb_substr($model->a6, 0, 20) . (mb_strlen($model->a6) > 80 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['title' => $model->a6];
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
