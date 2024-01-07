<?php

use app\models\Question;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\QuestionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Quiz Details';
// $this->params['breadcrumbs'][] = $this->title;
echo "<p style='color:#909090;font-size:16px;'>".$this->title.'</p>';
?>

<style>
    .multiline-tooltip::after {
        content: attr(data-tooltip);
        display: none;
        position: absolute;
        left: 120px;
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

    .dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-bottom: 5px;
        margin-right: 5px;
    }

    .dot-red {
        background-color: salmon;
    }

    .dot-green {
        background-color: lightgreen;
    }

    .quiz-button {
        font-size: 12px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }


    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        display: none;
    }

    .modal-dialog {
        position: fixed;
        top: 50%;
        left: 50%;
        background: #fff;
        border-radius: 5px;
        padding: 20px;
        text-align: center;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
    }

    .loader {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 2s linear infinite;
        margin: 0 auto;
        margin-bottom: 10px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .hidden-row {
        display: none;
    }

    a.nostyle {
        color: #0a58ca;
        text-decoration: none;
        background-color: transparent;
        border: 2px solid #0a58ca;
        display: inline-block;
        min-width: 16px;
        font-size: 12px;
        padding-left: 3px;
        padding-right: 3px;
    }

    .pagination li {
        margin-right: 10px;
        /* Adjust the value as needed */
    }
</style>


<?php
$currentRoute = Yii::$app->controller->getRoute();
$params = Yii::$app->request->getQueryParams();

# headerStatus is the view-status: all, only-checked, onlyunchecked
$headerStatus = ['A', 'X', '&#x2713'];
# When navigating to the next status, determine the next URL
# the show parameter circles through 1,0,-1
$nextShow = $show - 1;
if ($nextShow < -1) $nextShow = 1;
$params['show'] = $nextShow;
$clickedOnHeader = Url::toRoute(array_merge([$currentRoute], $params));

$csrfToken = Yii::$app->request->getCsrfToken();
$apiUrl = Url::toRoute(['/quiz-question/connect']);
$id = Yii::$app->request->get('id');

$script = <<< JS
ajaxActive=0;
$('.status-checkbox').change(function() {
    var questionId = $(this).attr('question-id'); 
    var quizId = $(this).attr('quiz-id'); 
    var active = $(this).is(':checked');
    var checkbox = $(this);

    ajaxActive++;
    if ( ajaxActive > 1 ) { // more than one update, show busy indicator
        $('#modalOverlay').show();
    }
    
    checkbox.css("visibility", "hidden");

    console.log('questionId: ' + questionId);
    console.log('quizId    : ' + quizId);
    console.log('active    : ' + active );
    console.log('apiUrl    : ' + '$apiUrl' );

    $.ajax({
        url: '$apiUrl',
        type: 'POST',
        data: {
            _csrf: '$csrfToken',
            quiz_id: quizId,
            question_id: questionId,
            active: active ? 1 : 0
        },
        success: function(response) {
            checkbox.css("visibility", "visible");
            ajaxActive--;
            if ( ajaxActive == 0) { // hide busy indicator
                $('#modalOverlay').hide();
            }
            $('#countDisplay').text(response.result.count);
            console.log('Update successful', response);
        },
        error: function(error) {
            ajaxActive--;
            if ( ajaxActive == 0) { // hide busy indicator
                $('#modalOverlay').hide();
            }
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
        if (confirm("This can not be undone, proceed?")) {
            $('input[type="checkbox"]').each(function() {
                $(this).prop('checked', thisValue).trigger('change');
            });
        }
    }
JS;
$this->registerJs($script);

$script = <<< JS
    window.headerCheckbox = function headerCheckbox(show) {
        console.log('$clickedOnHeader');
        window.location.href='$clickedOnHeader';
    }
JS;
$this->registerJs($script);
?>

<?php
    $show = Yii::$app->request->get('show', 1);
    $QuestionLabelText = 'Active Quiz Questions';
    if($show == 0) {
        $QuestionLabelText = 'Inactive Questions';
    }
    elseif($show == -1) {
        $QuestionLabelText = 'All Questions';
    }
?>

<!-- This is the busy overlay, show as more than one quesstion is updated via AJAX -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-dialog">
        <div class="loader"></div>
        <p>Please wait... </p>
    </div>
</div>

<div class="quiz-card" style="max-width:700px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
    <div class="container">
        <div class="row">
            <div class="col-md-7">
                <?php
                $statusClass = $quiz['active'] == 1 ? 'dot-green' : 'dot-red';
                $statusHelp = $quiz['active'] == 1 ? 'active' : 'inactive';
                ?>
                <h3>
                    <div title="<?= $statusHelp ?>" class="dot <?= $statusClass ?>"></div> <?= Html::encode($quiz['name']) ?>
                </h3>
                <p style="color:#404080;">
                    Password: <?= Html::encode($quiz['password']) ?>
                    <br>
                    questions: <span id="countDisplay"><?= count($questionIds); ?></span>
                </p>
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <?= Html::a('Edit', ['quiz/update', 'id' => $quiz['id']], ['class' => 'btn btn-outline-primary quiz-button'],) ?>
                <?php
                $url = Yii::$app->urlManager->createUrl(['/question/list', 'quiz_id' => $quiz['id']]);
                echo Html::a('View', $url, ['title' => 'View Questions', 'class' => 'btn btn-outline-success quiz-button',]);
                ?>
                <?= Html::a('Copy', ['quiz/copy',   'id' => $quiz['id']], ['class' => 'btn btn-outline-danger quiz-button'],); ?>
                <?php
                $url = Yii::$app->urlManager->createUrl(['/submission', 'quiz_id' => $quiz['id']]);
                echo Html::a('Results', $url, [
                    'title' => 'Show Results/Progress',
                    'class' => 'btn btn-outline-dark quiz-button',
                ]);
                ?>

            </div>
        </div>
    </div>
</div>


<div class="question-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        // 'rowOptions' => function ($model, $key, $index, $grid) use ($questionIds, $show) {
        //     if ($show) {
        //         $isChecked = in_array($model->id, $questionIds);
        //         if (($show == 1 && !$isChecked) || ($show == 2 && $isChecked)) {
        //             return ['class' => 'xhidden-row'];
        //         }
        //     }
        // },
        'columns' => [

            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'status',
                'headerOptions' => ['style' => 'width:40px;'],
                'header' => '<a href="#" id="header-checkbox" name="header-checkbox" class="nostyle""
                            onclick="headerCheckbox(' . $show . ');" >' . $headerStatus[$show + 1] . '</a>',
                'label' => '',
                'format' => 'raw', // to render raw HTML
                'value' => function ($model) use ($questionIds, $quiz_id) {
                    $isChecked = in_array($model->id, $questionIds);
                    $status = 'Question included (checked) or not (unchecked)';
                    return Html::checkbox('status[]', $isChecked, [
                        'class' => 'status-checkbox',
                        'question-id' => $model->id,
                        'quiz-id' => $quiz_id,
                        'title' => $status,
                    ]);
                },
            ],
            [
                'attribute' => 'question',
                'label' => $QuestionLabelText,
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
            [
                'attribute' => 'label',
                'label' => 'Label',
                'headerOptions' => ['style' => 'width:200px;'],
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
                'class' => ActionColumn::className(),
                'headerOptions' => ['style' => 'width:80px;'],
                'urlCreator' => function ($action, Question $model, $key, $index, $column) use ($show) {
                    return Url::toRoute([$action, 'id' => $model->id, 'show' => $show]);
                }
            ],
        ],
    ]); ?>


</div>

<div id="button-bar" style="display:block;">
    <p>
        <hr>
        <?php
        echo Html::a('New', ['create', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-outline-success quiz-button', 'title' => 'Create new question']);
        ?>
        <span style="margin-left:50px;"> </span>

        <?php
        echo Html::button('Link All', [
            'class' => 'btn btn-outline-secondary quiz-button',
            'onclick' => 'checkAllCheckboxes(true);',
        ]);
        echo Html::button('Unlink All', [
            'class' => 'btn btn-outline-secondary quiz-button',
            'onclick' => 'checkAllCheckboxes(false);',
        ]);
        ?>
        <span style="margin-left:50px;"> </span>
        <?php
        echo Html::a('import', ['import', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-outline-secondary quiz-button', 'title' => '']);
        echo Html::a('export', ['export', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-outline-secondary quiz-button', 'title' => '']);
        echo Html::a('Multi Edit', ['multiple-update', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-outline-secondary quiz-button', 'title' => '']);
        ?>
        <span style="margin-left:50px;"> </span>
        <?php
        if ($show == 1) {
            echo Html::a(
                'Delete All',
                ['bulk-delete', 'quiz_id' => $quiz['id']],
                [
                    'class' => 'btn btn-outline-danger quiz-button',
                    'title' => 'Delete all linked',
                    'onclick' => 'return confirm("Are you sure you want to delete all linked items?");',
                ]
            );
        }
        ?>
    </p>
</div>