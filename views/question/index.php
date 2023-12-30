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

<style>
    .multiline-tooltip::after {
        content: attr(data-tooltip);
        display: none;
        position: absolute;
        z-index: 1;
        background: #dbe6ff;
        border: 1px solid #ccc;
        padding: 5px;
        font-family: monospace;
        white-space: pre-wrap;
        max-width: 600px;
    }

    .multiline-tooltip:hover::after {
        display: block;
    }
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
    }
</style>


<?php
$csrfToken = Yii::$app->request->getCsrfToken();
$id = Yii::$app->request->get('id');

$script = <<< JS
$('.status-checkbox').change(function() {
    var questionId = $(this).attr('question-id'); 
    var quizId = $(this).attr('quiz-id'); 
    var active = $(this).is(':checked');

    console.log('questionId: ' + questionId);
    console.log('quizId    : ' + quizId);
    console.log('active    : ' + active );

    $.ajax({
        url: '/quiz-question/connect',
        type: 'POST',
        data: {
            _csrf: '$csrfToken',
            quiz_id: quizId,
            question_id: questionId,
            active: active ? 1 : 0
        },
        success: function(response) {
            // Handle success
            $('#countDisplay').text(response.result.count);
            console.log('Update successful', response);
        },
        error: function(xhr, status, error) {
            // Handle error
            console.log('Update failed:', error);
        }
    });
});
JS;
$this->registerJs($script);
?>

<?php
// Yii places the function outside of the scope of the HTML page, therefor we attach it to the window object
$script = <<< JS
    window.checkAllCheckboxes = function checkAllCheckboxes(thisValue) {
        $('input[type="checkbox"]').each(function() {
            $(this).prop('checked', thisValue).trigger('change');
        });
    }
JS;
$this->registerJs($script);
?>


<div class="quiz-card" style="max-width:600px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
    <h3><?= Html::encode($quiz['name']) ?></h3>
    <p>
        Password: <?= Html::encode($quiz['password']) ?>
        <br>
        <?php   if ( $quiz['active']) echo "<span style=\"background-color:lightgreen\">Active</span>";
                else echo "<span style=\"background-color:lightsalmon\">Inactive</span>";
        ?>
    </p>
    <div style="display: flex; justify-content: flex-end; align-items: left;">
        <?= Html::a('Edit', ['quiz/update', 'id' => $quiz['id']], ['class' => 'btn btn-outline-primary button-sm m-2'],) ?>
        <?= Html::a('Copy', ['quiz/copy',   'id' => $quiz['id']], [ 'class' => 'btn btn-outline-danger button-sm m-2'],); ?>
    </div>
</div>


<div class="question-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
 
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            [
                'attribute' => 'id',
                'label' => 'id',
                'headerOptions' => ['style' => 'width:40px;'],
            ],
            [
                'attribute' => 'status', // or any relevant attribute
                'format' => 'raw', // to render raw HTML
                'value' => function ($model) use ($questionIds, $quiz_id) {
                    $isChecked = in_array($model->id, $questionIds);
                    return Html::checkbox('status[]', $isChecked, [
                        'class' => 'status-checkbox',
                        'question-id' => $model->id,
                        'quiz-id' => $quiz_id,
                    ]);
                },
            ],
            [
                'attribute' => 'question',
                'value' => function ($model) {
                    return mb_substr($model->question, 0, 100) . (mb_strlen($model->question) > 100 ? '...' : '');
                },
                'contentOptions' => function ($model) {
                    return ['class' => 'multiline-tooltip',
                            'data-tooltip' => Html::encode($model->question)
                ];
                },
            ],
            [
                'attribute' => 'label',
                'label' => 'Label',
                'headerOptions' => ['style' => 'width:200px;'],
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['style' => 'width:80px;'],
                'urlCreator' => function ($action, Question $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>

<p>
    <?= Html::a('New Question', ['create'], ['class' => 'btn btn-outline-success']) ?>
</p>

