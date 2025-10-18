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

// $this->title = 'All Questions';
// $this->params['breadcrumbs'][] = $this->title;
// echo "<p style='color:#909090;font-size:12px;margin-top:20px;'>" . $this->title . '</p>';

// Make entire table rows clickable
$script = <<< JS
    $(document).ready(function() {
        $('.grid-view tbody tr').click(function(e) {
            // Don't trigger if clicking on delete button or other interactive elements
            if ($(e.target).closest('a[href*="delete"]').length > 0) {
                return;
            }
            
            // Find the question link in this row and click it
            var questionLink = $(this).find('a[href*="update"]').first();
            if (questionLink.length > 0) {
                window.location.href = questionLink.attr('href');
            }
        });
    });
JS;
$this->registerJs($script);
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

    /* Make table rows clickable and hover-friendly */
    .grid-view tbody tr {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .grid-view tbody tr:hover {
        background-color: #f8f9fa;
    }

    .grid-view tbody tr:hover td {
        background-color: transparent;
    }

    .question-index {
        margin-top: 20px;
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
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'width:50px;'],
            ],
            [
                'label' => '#r',
                'attribute' => 'quizQuestionsCount',
                'headerOptions' => ['style' => 'width:40px;'],
                'headerOptions' => ['title' => 'Number of times used in quizes'],
                'value' => function ($model) {
                    return $model->getQuizquestions()->count();
                },
            ],
            [
                'attribute' => 'question',
                'format' => 'raw',
                'value' => function ($model) {
                    $pattern = '/<pre>(.*?)<\/pre>(.*)/s';
                    if (preg_match($pattern, $model->question, $matches)) {
                        $questionText = '...' . $matches[1] . $matches[2];
                    } else {
                        $questionText = $model->question;
                    }
                    $truncatedText = mb_substr($questionText, 0, 100) . (mb_strlen($questionText) > 100 ? '...' : '');
                    $editUrl = Url::toRoute(['update', 'id' => $model->id]);
                    return Html::a($truncatedText, $editUrl, [
                        'style' => 'color: #0a58ca; text-decoration: none; cursor: pointer;',
                        'title' => 'Click to edit this question'
                    ]);
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
                'headerOptions' => ['style' => 'width:180px;'],
                'contentOptions' => ['style' => 'color: #404080;'],
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
                'label' => 'Actions',
                'headerOptions' => ['style' => 'width:60px;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'format' => 'raw',
                'value' => function ($model) {
                    $deleteUrl = Url::toRoute(['delete', 'id' => $model->id]);
                    return Html::a('🗑️', $deleteUrl, [
                        'title' => 'Delete this question',
                        'style' => 'color: #dc3545; text-decoration: none; font-size: 16px;',
                        'onclick' => 'return confirm("Are you sure you want to delete this question? This action cannot be undone.");'
                    ]);
                },
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