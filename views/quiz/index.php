<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quiz List';
// $this->params['breadcrumbs'][] = $this->title;
echo "<p style='color:#909090;font-size:16px;'>" . $this->title . '</p>';

// $updateNameUrl = '/quiz/a';
// $updatePasswordUrl = '/quiz/a';

$csrfToken = Yii::$app->request->getCsrfToken();
$id = Yii::$app->request->get('id');

$apiUrl = Url::toRoute(['/quiz-question/active']);

$js = <<<JS

function updateActiveStatus(id, active) {
    console.log("id: "+id);
    console.log("active: "+active);
    $.ajax({
        url: '$apiUrl',
        method: 'POST',
        data: {  _csrf: '$csrfToken',
                id: id,
                active: active ? 1 : 0
        },

        success: function(response) {
            console.log('Update successful', response);
        },
        error: function(xhr, status, error) {
            console.log('Update failed:', error);
        }
    });
}

// Handle the change event of the radio buttons for active status
$('input[name="active"]').on('change', function() {
    var quizId = $(this).val();
    var isActive = $(this).prop('checked');
    updateActiveStatus(quizId, isActive);
});

$(document).on('click', '.group-header', function() {
    var header = $(this);
    header.toggleClass('collapsed');
    header.nextUntil('.group-header').toggle(); // This will show/hide the rows until the next group header
});

$(document).ready(function() {
    $('.group-header').nextUntil('.group-header').hide(); // This hides all rows that follow a '.group-header.collapsed' until the next '.group-header'
});

JS;

// Register the JavaScript code
$this->registerJs($js);

?>

<style>
    .quiz-button-small {
        font-size: 12px;
        padding: 0px 2px;
        min-width: 55px;
        margin-left: 5px;
        margin-right: 5px;
    }

    .quiz-button {
        font-size: 14px;
        padding: 2px 5px;
        min-width: 55px;
        margin-left: 5px;
        margin-right: 5px;
    }

    .group-header .triangle {
        cursor: pointer;
        display: inline-block;
        transition: transform 0.3s ease-in-out;
    }

    .group-header.collapsed .triangle {
        transform: rotate(-90deg);
        /* Pointing right when collapsed */
    }

    .group-content {
        display: none;
        /* Initially hide the content */
    }
    .group-title{
        color: darkblue;
        font-weight: 600;
    }
    
    /* Dropdown menu styling */
    .btn-group {
        vertical-align: middle;
    }
    
    .dropdown-menu {
        min-width: 150px;
    }
    
    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
    }
</style>

<div class="quiz-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'beforeRow' => function ($model, $key, $index, $grid) {
        //         static $lastGroup = null;
        //         $currentGroup = strstr($model->name, '.', true);

        //         if ($lastGroup !== $currentGroup) {
        //             $lastGroup = $currentGroup;
        //             if ( $currentGroup == "" ) {
        //                 return "<tr class='group-header'><td colspan='9'><div class='group-title'><span class='triangle'>&#9662;</span>no category</div></td></tr>";
        //             } else {
        //                 return "<tr class='group-header collapsed'><td colspan='9'><div class='group-title'><span class='triangle'>&#9662;</span>{$currentGroup}</div></td></tr>";
        //             }
        //         }

        //         return null;
        //     },

        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn', // This adds a serial column to grid
                'headerOptions' => ['style' => 'width:35px;'],
                'header' => '',

            ],
            [
                'attribute' => 'active',
                'headerOptions' => ['style' => 'width:40px;'],
                'format' => 'raw',
                'contentOptions' => ['class' => 'active-field', 'title' => 'Quiz can be started when checked'],
                'value' => function ($model) {
                        return Html::checkbox('active', $model->active, ['value' => $model->id, 'class' => 'active-radio']);
                    },
            ],
            [
                'attribute' => 'name',
                'headerOptions' => ['style' => 'width:250px;'],
                'format' => 'raw',
                'value' => function ($model) {
                        // $url = Yii::$app->urlManager->createUrl(['/submission', 'quiz_id' => $model->id]);
                        $url = Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $model->id]);
                        return Html::a($model->name, $url, ['title' => 'Show Quiz']);
                    },
            ],
            [
                'attribute' => 'password',
                'headerOptions' => ['style' => 'width:180px;'],
                'format' => 'raw',
                'value' => function ($model) {
                        return $model->password;
                    },
            ],
            [
                'label' => 'Questions',
                'headerOptions' => ['style' => 'width:140px;'],
                'value' => function ($model) use ($quizCounts) {
                        $id = $model->id;
                        $aantalQuestion = isset($quizCounts[$id]) ? $quizCounts[$id] : 0;
                        $maxQuestions = isset($model['no_questions']) ? $model['no_questions'] : $aantalQuestion;
                        return $maxQuestions . ' from ' . $aantalQuestion;
                    },
            ],
            [
                'label' => 'rw',
                'attribute' => 'review',
                'headerOptions' => ['style' => 'width35px;font-size: 10px;', 'title' => 'Review'],
                'format' => 'raw',
                'enableSorting' => false,
                'value' => function ($model) {
                        if ($model->review) {
                            return "&#10003;";
                        } else {
                            return "-";
                        }
                    },

            ],
            [
                'label' => 'bl',
                'attribute' => 'blind',
                'headerOptions' => ['style' => 'width:35px;font-size: 10px;', 'title' => 'Blind'],
                'format' => 'raw',
                'enableSorting' => false,
                'value' => function ($model) {
                        if ($model->blind) {
                            return "&#10003;";
                        } else {
                            return "-";
                        }
                    },

            ],
            [
                'label' => 'ip',
                'headerOptions' => ['style' => 'width:35px;font-size: 10px;', 'title' => 'Ip-restricted'],
                'attribute' => 'ip_check',
                'format' => 'raw',
                'enableSorting' => false,
                'value' => function ($model) {
                        if ($model->ip_check) {
                            return "&#10003;";
                        } else {
                            return "-";
                        }
                    },

            ],
            [
                'label' => 'Rnd',
                'headerOptions' => ['style' => 'width:35px;font-size: 10px;', 'title' => 'Ip-restricted'],
                'attribute' => 'random',
                'format' => 'raw',
                'enableSorting' => false,
                'value' => function ($model) {
                        if ($model->random) {
                            return "&#10003;";
                        } else {
                            return "-";
                        }
                    },

            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['style' => 'width:300px;'],
                'template' => '{quizButton}',
                'buttons' => [
                    'quizButton' => function ($url, $model) {
                            // Primary buttons: Questions and Edit
                            $questionsUrl = Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $model->id]);
                            $btnQuestions = Html::a('❓ Questions', $questionsUrl, [
                                'title' => 'Show Questions',
                                'class' => 'btn btn-outline-dark quiz-button-small',
                            ]);
                            
                            $editUrl = Yii::$app->urlManager->createUrl(['/quiz/update', 'id' => $model->id]);
                            $btnEdit = Html::a('✏️ Edit', $editUrl, [
                                'title' => 'Edit Quiz',
                                'class' => 'btn btn-outline-primary quiz-button-small',
                            ]);
                            
                            // Dropdown for less frequently used options
                            $viewUrl = Yii::$app->urlManager->createUrl(['/question/list', 'quiz_id' => $model->id]);
                            $deleteUrl = Yii::$app->urlManager->createUrl(['/quiz/delete', 'id' => $model->id]);
                            $resultsUrl = Yii::$app->urlManager->createUrl(['/submission', 'quiz_id' => $model->id]);
                            
                            $dropdown = '<div class="btn-group" style="display: inline-block;">
                                <button type="button" class="btn btn-outline-secondary quiz-button-small dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ⋮ More
                                </button>
                                <div class="dropdown-menu">
                                    ' . Html::a('👁️ View', $viewUrl, ['class' => 'dropdown-item', 'title' => 'View Questions']) . '
                                    ' . Html::a('📊 Results', $resultsUrl, ['class' => 'dropdown-item', 'title' => 'Show Results/Progress']) . '
                                    <div class="dropdown-divider"></div>
                                    ' . Html::a('❌ Delete', $deleteUrl, [
                                        'class' => 'dropdown-item text-danger',
                                        'title' => 'Delete Quiz',
                                        'data-confirm' => 'Are you sure you want to delete this quiz?',
                                        'data-method' => 'post',
                                    ]) . '
                                </div>
                            </div>';
                            
                            return $btnQuestions . ' ' . $btnEdit . ' ' . $dropdown;
                        },
                ],
            ],
        ],
    ]); ?>
</div>

<p>
    <?= Html::a('➕ New Quiz', ['create'], ['title' => 'Create New Quiz', 'class' => 'btn btn-outline-success quiz-button']) ?>
    &nbsp;&nbsp;&nbsp;
    <?= Html::a('➕ Reset All', ['index', 'reset' => 1], ['title' => 'Disbale all quizes', 'class' => 'btn btn-outline-success quiz-button']) ?>
</p>