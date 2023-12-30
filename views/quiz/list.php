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


// JavaScript code to handle the AJAX calls
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

// Function to update the name of a quiz
function updateName(id, newName) {
    $.ajax({
        url: '$updateNameUrl',
        method: 'POST',
        data: {id: id, name: newName},
        success: function(data) {
            // Handle the success response
            console.log('Name updated successfully.');
        },
        error: function() {
            // Handle any errors that occur during the AJAX call
            console.error('Error updating name.');
        }
    });
}

// Function to update the password of a quiz
function updatePassword(id, newPassword) {
    $.ajax({
        url: '$updatePasswordUrl',
        method: 'POST',
        data: {id: id, password: newPassword},
        success: function(data) {
            // Handle the success response
            console.log('Password updated successfully.');
        },
        error: function() {
            // Handle any errors that occur during the AJAX call
            console.error('Error updating password.');
        }
    });
}

// Handle the blur event of the name and password fields
$('span').on('blur', function() {
    console.log('In edit');

    var quizId = $(this).closest('tr').find('.hidden-id').val();
    var fieldName = $(this).data('field');
    var newValue = $(this).text();

    console.log("quizId: "+quizId, $(this), newValue)
    
    if (fieldName === 'name') {
        updateName(quizId, newValue);
    } else if (fieldName === 'password') {
        updatePassword(quizId, newValue);
    }
});
JS;

// Register the JavaScript code
$this->registerJs($js);

?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
    }
</style>

<div class="quiz-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
                'contentOptions' => ['class' => 'hidden-id'],
                'visible' => true, // Hide the ID column
            ],
            [
                'attribute' => 'active',
                'format' => 'raw',
                'contentOptions' => ['class' => 'active-field'],
                'value' => function ($model) {
                    return Html::radio('active', $model->active, ['value' => $model->id, 'class' => 'active-radio']);
                },
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->name;
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
                        $b1 = Html::a('Open', $url, [ 'title' => 'View Questions',
                            'class' => 'btn btn-outline-primary quiz-button',
                            ]);
                        $url = Yii::$app->urlManager->createUrl(['/quiz/update', 'id' => $model->id]);
                        $b2 = Html::a('Edit', $url, [ 'title' => 'Edit Quiz',
                            'class' => 'btn btn-outline-primary quiz-button',
                            ]);
                        $url = Yii::$app->urlManager->createUrl(['quiz/view', 'id' => $model->id]);
                        $b3 = Html::a('Questions', $url, [ 'title' => 'Edit Questions',
                            'class' => 'btn btn-outline-primary quiz-button',
                            ]);
                        return $b1.' '.$b2.' '.$b3;
                    },
                ],
            ],
        ],
    ]); ?>
</div>
