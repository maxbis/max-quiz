<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quiz List';
$this->params['breadcrumbs'][] = $this->title;

$updateNameUrl = '/quiz/a';
$updatePasswordUrl = '/quiz/a';

$csrfToken = Yii::$app->request->getCsrfToken();
$id = Yii::$app->request->get('id');


$js = <<<JS

function updateActiveStatus(id, active) {
    console.log("id: "+id);
    console.log("active: "+active);
    $.ajax({
        url: '/quiz-question/active',
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

JS;

// Register the JavaScript code
$this->registerJs($js);

?>

<style>
    .quiz-button-small {
        font-size: 10px;
        padding: 2px 5px;
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
</style>

<div class="quiz-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width:40px;'],
                'contentOptions' => ['class' => 'hidden-id'],
                'visible' => true, // Hide the ID column
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
                'format' => 'raw',
                'value' => function ($model) {
                    $url = Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $model->id]);
                    return Html::a($model->name, $url);
                },
            ],
            [
                'attribute' => 'password',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->password;
                },
            ],
            [
                'label' => 'Questions',
                'value' => function ($model) use ($quizCounts) {
                    $id = $model->id;
                    return isset($quizCounts[$id]) ? $quizCounts[$id] : 0;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{quizButton}',
                'buttons' => [
                    'quizButton' => function ($url, $model) {
                        $url = Yii::$app->urlManager->createUrl(['/question/list', 'quiz_id' => $model->id]);
                        $b1 = Html::a('View', $url, [
                            'title' => 'View Questions',
                            'class' => 'btn btn-outline-success quiz-button-small',
                        ]);
                        $url = Yii::$app->urlManager->createUrl(['/quiz/update', 'id' => $model->id]);
                        $b2 = Html::a('Edit', $url, [
                            'title' => 'Edit Quiz',
                            'class' => 'btn btn-outline-primary quiz-button-small',
                        ]);
                        $url = Yii::$app->urlManager->createUrl(['/quiz/delete', 'id' => $model->id]);
                        $b3 = Html::a('Delete', $url, [
                            'title' => 'Delete Quiz',
                            'class' => 'btn btn-outline-danger quiz-button-small',
                            'data-confirm' => 'Are you sure you want to delete this quiz?',
                            'data-method' => 'post',
                        ]);
                        return $b2 . ' ' . $b1 . ' ' . $b3;
                    },
                ],
            ],
        ],
    ]); ?>
</div>

<p>
    <?= Html::a('New Quiz', ['create'], ['title' => 'Create New Quiz', 'class' => 'btn btn-outline-success quiz-button']) ?>
</p>