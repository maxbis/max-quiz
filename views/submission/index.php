<?php

use app\models\Submission;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

?>
<div class="submission-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'header' => 'Voortgang',
                'attribute' => 'no_answered',
                'headerOptions' => ['style' => 'width:60px;'],
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ( $model->no_answered == $model->no_questions ) {
                        $backgroundColor = 'lightgreen';
                    } else {
                        $backgroundColor = "";
                    }
                    return ['style' => "background-color: $backgroundColor;"];
                },
                'value' => function ($model) {
                    return $model->no_questions > 0 ? round(($model->no_answered / $model->no_questions) * 100, 0) . '%' : '0';
                },
            ],
            [
                'header' => 'Score',
                'headerOptions' => ['style' => 'width:80px;'], 
                'contentOptions' => function ($model, $key, $index, $column) {
                    $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                    if ( $model->no_answered ) {
                        $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
                    } else {
                        $backgroundColor = "";
                    }
                    return ['style' => "background-color: $backgroundColor;"];
                },
                'value' => function ($model) {
                    return $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) . '%' : '0';
                },
            ],
            [
                'attribute' => 'first_name',
                'label' => 'Student',
                'headerOptions' => ['style' => 'width:200px;'],
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ( $model->no_answered == $model->no_questions ) {
                        $backgroundColor = 'lightgreen';
                    } else {
                        $backgroundColor = "";
                    }
                    return ['style' => "background-color: $backgroundColor;"];
                },
                'value' => function ($model) {
                    return $model->first_name.' '.$model->last_name;
                },
                
            ],
            [
                'attribute' => 'class',
                'label' => 'klas',
                'headerOptions' => ['style' => 'width:60px;'],
                
            ],
            [
                'attribute' => 'no_answered',
                'label' => '#vragen',
                'headerOptions' => ['style' => 'width:60px;'],
                'value' => function ($model) {
                    return $model->no_answered.'/'.$model->no_questions;
                },
            ],
            [
                'attribute' => 'no_correct',
                'label' => 'goed',
                'headerOptions' => ['style' => 'width:60px;'],
                
            ],
            [
                'attribute' => 'last_updated',
                'label' => 'Update',
                'headerOptions' => ['style' => 'width:60px;'],
                
            ],
        ],
    ]); ?>


</div>
