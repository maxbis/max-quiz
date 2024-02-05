<?php

use app\models\Quiz;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\QuizSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Quizzes';
$this->params['breadcrumbs'][] = $this->title;

$csrfToken = Yii::$app->request->getCsrfToken();

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

// Handle the change event of the checkboxs for active status
$('input[name="active"]').on('change', function() {
    var quizId = $(this).val();
    var isActive = $(this).prop('checked');
    console.log('Fired!');
    updateActiveStatus(quizId, isActive);
});
JS;

// Register the JavaScript code
$this->registerJs($js);
?>

<div class="quiz-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Quiz', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => '',
                'format' => 'raw',
                'contentOptions' => ['class' => 'active-field'],
                'header' => '',
                'filter' => false,
                'value' => function ($model) {
                    return Html::checkbox('active', $model->active, ['value' => $model->id]);
                },
            ],
            'name',
            'password',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Quiz $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
