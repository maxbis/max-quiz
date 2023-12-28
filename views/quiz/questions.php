<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Quiz $model */

$this->title = $quiz->name;
$this->params['breadcrumbs'][] = ['label' => 'Quizzes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<style>
    .question-td {
        margin-right:20px;
        position: relative;
        display: inline-block;
    }

    .multiline-tooltip {
        cursor: pointer;
        position: relative;
        display: inline-block;
    }

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
        max-width: 600px; /* Adjust as needed */
    }

    .multiline-tooltip:hover::after {
        display: block;
    }
</style>

<?php
$csrfToken = Yii::$app->request->getCsrfToken();

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
// Yii places the function outside of the scope og teh HTML page, therefor we attach it to the window object
$script = <<< JS
    window.checkAllCheckboxes = function checkAllCheckboxes(thisValue) {
        $('input[type="checkbox"]').each(function() {
            $(this).prop('checked', thisValue).trigger('change');
        });
    }
JS;
$this->registerJs($script);
?>



<div class="quiz-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $quiz,
        'attributes' => [
            'id',
            'name',
            'password',
            'active',
            'no_questions',
        ],
    ]) ?>

</div>

<?php
    echo Html::button('Check All', [
        'class' => 'btn btn-light button-sm m-2',
        'onclick' => 'checkAllCheckboxes(true);',
    ]);
    echo Html::button('Uncheck All', [
        'class' => 'btn btn-light button-sm m-2',
        'onclick' => 'checkAllCheckboxes(false);',
    ]);
?>
<?php
    $id = Yii::$app->request->get('id');
?>
<form action="<?= Url::to(['/quiz/questions', 'id' => $id]) ?>" method="get">
    <input type="text" name="search" id="search" placeholder="Search...">
    <input type="hidden" name="id" value=<?=$id?> >
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
</form>

<table>

    <tbody>
        <?php foreach ($questions as $question): ?>
            <tr>
                
                <td class="question-td">
                    <?php
                    echo Html::checkbox('status', false, [
                        'class' => 'status-checkbox',
                        'question-id' => $question->id,
                        'quiz-id' => $quiz->id,
                    ]);
                    ?>
                </td>

                <td class="question-td"><?=$question->label?></td>

                <td class="multiline-tooltip" data-tooltip="<?= Html::encode($question->question) ?>">
                    <?= Html::encode(mb_substr($question->question, 0, 60)) ?>
                </td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
