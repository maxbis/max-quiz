<?php

use app\assets\CustomDialogAsset;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Quiz $model */

$this->title = $quiz->name;
$this->params['breadcrumbs'][] = ['label' => 'Quizzes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
CustomDialogAsset::register($this);
?>

<style>
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
        max-width: 600px;
        /* Adjust as needed */
    }

    .multiline-tooltip:hover::after {
        display: block;
    }

    .myTable {
        background-color: #f8f8f8;

        box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
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

<table class="myTable" style="margin: 40px 0;">
    <tr>
        <td style="width: 60%;margin: 40px 0;">

            <div class="quiz-view">
                <table class="table">
                    <tr>
                        <td style="font-weight:bold;">Naam</td>
                        <td style="font-weight:bold;"><?= $quiz->name ?> </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Wachtwoord</td>
                        <td><?= $quiz->password ?> </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Actief</td>
                        <td><?= $quiz->active ?> </td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Aantal vragen</td>
                        <td style="vertical-align: left;" id="countDisplay"><?= $quiz->no_questions ?> </td>
                    </tr>
                </table>
            </div>
        </td>
        <td style="text-align: left;vertical-align: bottom;">
            &nbsp;
            <?php
                echo Html::a('Edit quiz',
                    ['quiz/update', 'id' => $id], 
                    [ 'class' => 'btn btn-primary button-sm m-2'],
                );
            ?>&nbsp;
            <?php
                echo Html::a('Copy quiz',
                    ['quiz/copy', 'id' => $id], 
                    [ 'class' => 'btn btn-warning button-sm m-2'],
                );
            ?>&nbsp;
            <?php
                echo Html::a('🏷️ Edit Labels',
                    ['quiz/edit-labels', 'id' => $id], 
                    [ 'class' => 'btn btn-info button-sm m-2', 'title' => 'Bulk edit question labels'],
                );
            ?>&nbsp;
            <?= Html::button('Swap active/inactive', [
                'class' => 'btn btn-danger button-sm m-2',
                'id' => 'swap-questions-button-top',
                'title' => 'Swap all active/inactive questions',
            ]) ?>
        </td>
    </tr>
</table>



<form style="margin: 20px 0;" action="<?= Url::to(['/quiz/view', 'id' => $id]) ?>" method="get">
    <label for="inputField">Label </label>
    <input type="text" name="search" id="search" placeholder="Search...">
    <input type="hidden" name="id" value=<?= $id ?>>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
</form>

<table class="table myTable" style="width:70%;">
    <thead>
        <tr>
            <th>#</th>
            <th>Label</th>
            <th>Question</th>
        </tr>
    </thead>

    <tbody>
        <?php $counter = 1;foreach ($questions as $question) : ?>
            <tr>
            <td><?=$counter++;?></td>
                <td>
                    <?php
                    $isChecked = in_array($question->id, $questionIds);
                    echo Html::checkbox('status', $isChecked, [
                        'class' => 'status-checkbox',
                        'question-id' => $question->id,
                        'quiz-id' => $quiz->id,
                    ]);
                    ?>
                </td>

                <td><?= $question->label ?></td>

                <td class="multiline-tooltip" data-tooltip="<?= Html::encode($question->question) ?>">
                    <?= Html::encode(mb_substr($question->question, 0, 80)) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

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

<?= Html::beginForm(['quiz/swap-questions', 'id' => $id], 'post', ['id' => 'swap-questions-form-top', 'style' => 'display:none;']) ?>
<?= Html::hiddenInput('returnUrl', Yii::$app->request->url) ?>
<?= Html::endForm() ?>

<?= $this->render('@app/views/include/_custom-dialog.php') ?>

<?php
$swapScript = <<<JS
$(document).ready(function() {
    $('#swap-questions-button-top').on('click', function(e) {
        e.preventDefault();
        window.showCustomDialog(
            '🔄 Swap Questions',
            'Swap active and inactive questions for this quiz?',
            function() {
                $('#swap-questions-form-top').submit();
            }
        );
    });
});
JS;
$this->registerJs($swapScript);
?>
