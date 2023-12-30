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
                'label' => 'Code', // You can change the label as needed
                'headerOptions' => ['style' => 'width:40px;'],
                'value' => function ($model) {
                    return strtoupper(substr($model->token, -3));
                },
            ],
            [
                'attribute' => 'Finished',
                'format' => 'raw',
                'contentOptions' => ['class' => 'active-field'],
                'header' => 'Ready',
                'headerOptions' => ['style' => 'width:40px;'],
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ( $model->finished ) {
                        $backgroundColor = 'lightgreen';
                    } else {
                        $backgroundColor = "";
                    }
                    return ['style' => "background-color: $backgroundColor;"];
                },
                'value' => function ($model) {
                    return Html::checkbox('finished', $model->finished, ['value' => $model->id, 'disabled' => true,]);
                },
            ],
            [
                'header' => 'Voortgang',
                'attribute' => 'no_answered',
                'headerOptions' => ['style' => 'width:60px;'],
                'contentOptions' => function ($model, $key, $index, $column) {
                    return ['style' => "position: relative;"];
                },
                'format' => 'raw', // Set format to raw to allow HTML content
                'value' => function ($model) {
                    $percentage = $model->no_questions > 0 ? round(($model->no_answered / $model->no_questions) * 100, 0) : 0;
                    return "<div style='width: $percentage%; height: 100%; background-color: lightgreen; position: absolute; top: 0; left: 0;'></div>"
                         . "<div style='position: relative;'>$percentage%</div>";
                },
            ],            
            // [
            //     'header' => 'Voortgang',
            //     'attribute' => 'no_answered',
            //     'headerOptions' => ['style' => 'width:60px;'],
            //     'contentOptions' => function ($model, $key, $index, $column) {
            //         if ( $model->no_answered == $model->no_questions ) {
            //             $backgroundColor = 'lightgreen';
            //         } else {
            //             $backgroundColor = "";
            //         }
            //         return ['style' => "background-color: $backgroundColor;"];
            //     },
            //     'value' => function ($model) {
            //         return $model->no_questions > 0 ? round(($model->no_answered / $model->no_questions) * 100, 0) . '%' : '0';
            //     },
            // ],
            [
                'header' => 'Score',
                'headerOptions' => ['style' => 'width:80px;'], 
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ( $model->finished ) {
                        //finished
                        $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                    } else {
                        // progress
                        $score = $model->no_correct > 0 ? round(($model->no_correct / $model->no_answered) * 100, 0) : 0;
                    }
                    // $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                    if ( $model->no_answered ) {
                        $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
                    } else {
                        $backgroundColor = "";
                    }
                    return ['style' => "background-color: $backgroundColor;"];
                },
                'value' => function ($model) {
                    if ( $model->finished ) {
                        //finished
                        return $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) . '%' : '0';
                    } else {
                        // progress
                        return $model->no_correct > 0 ? round(($model->no_correct / $model->no_answered) * 100, 0) . '%' : '0';
                    }
                },
            ],
            [
                'attribute' => 'first_name',
                'label' => 'Student',
                'headerOptions' => ['style' => 'width:200px;'],
                'contentOptions' => function ($model, $key, $index, $column) {
                    if ( ! $model->finished )return ['style' => ""];
                    $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                    if ( $model->no_answered ) {
                        $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
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
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->no_answered.'/'.$model->no_questions, ['submission/update', 'id' => $model->id]);
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
                'headerOptions' => ['style' => 'width:200px;'],
            ],
            [   
                'label' => 'Questions',
                'attribute' => 'question_order',
                'enableSorting' => false,
                'filter' => false,
            ],
            [
                'label' => 'Question',
                'format' => 'raw',
                'value' => function ($model) {
                    $numbers = explode(' ', $model->question_order);
                    $index = $model->no_answered; 
    
                    if (isset($numbers[$index])) {
                        return $numbers[$index];
                    }
    
                    return '-'; 
                },
            ],
            [
                'label' => 'Answers',
                'attribute' => 'answer_order',
                'enableSorting' => false,
                'filter' => false,
            ],

        ],
    ]); ?>


</div>
