<?php

use app\models\Question;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\CheckboxColumn;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\QuestionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Questions';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .pagination li {
        margin-right: 10px;
    }

    .multiline-tooltip::after {
        content: attr(data-tooltip);
        display: none;
        position: absolute;
        left: 160px;
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
        font-size: 12px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>


<div class="question-index">

    <?php  // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?php

    // $form = ActiveForm::begin([
    //     'action' => Url::to(['/question/delete-multiple']),
    //     'options' => ['id' => 'gridview-delete-form'],
    // ]);

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // [
            //     'class' => CheckboxColumn::class
            // ],
            [
                'class' => 'yii\grid\SerialColumn'
            ],
            [
                'label' => '#r',
                'attribute' => 'quizQuestionsCount',
                'headerOptions' => ['title' => 'Number of times used in quizes'],
                'value' => function ($model) {
                    return $model->getQuizquestions()->count();
                },
            ],
            [
                'attribute' => 'question',
                'value' => function ($model) {
                    $pattern = '/<pre>(.*?)<\/pre>(.*)/s';
                    if (preg_match($pattern, $model->question, $matches)) {
                        $questionText = '...' . $matches[1] . $matches[2];
                    } else {
                        $questionText = $model->question;
                    }
                    return mb_substr($questionText, 0, 100) . (mb_strlen($questionText) > 100 ? '...' : '');
                },
            ],
            // [
            //     'attribute' => 'correct',
            //     'label' => 'correct',
            //     'headerOptions' => ['style' => 'width:100px;'],
            // ],

            [
                'attribute' => 'label',
                'label' => 'Label',
                'headerOptions' => ['style' => 'width:200px;'],
            ],
            [
                'attribute' => 'id',
                'label' => 'id',
                'headerOptions' => ['style' => 'width:30px;'],
                'contentOptions' => function ($model) {
                    return [
                        'class' => 'multiline-tooltip',
                        'style' => 'color: #404080;',
                        'data-tooltip' =>  $model->question,
                    ];
                },
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Question $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]);
    ?>
</div>

<div id="button-bar" style="display:block;">
    <p>
        <hr>
        <?php
        // echo Html::submitButton('Delete Selected', [
        //     'class' => 'btn btn-outline-danger quiz-button',
        //     'id' => 'delete-selected-button',
        //     'onclick' => 'return confirm("Are you sure you want to delete the selected items?");',
        //     'method' => 'post',
        // ]);
        ?>
    </p>
</div>

<?php
// ActiveForm::end();
?>