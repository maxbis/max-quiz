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
    $('.group-header.collapsed').nextUntil('.group-header').hide(); // This hides all rows that follow a '.group-header.collapsed' until the next '.group-header'
});

JS;

// Register the JavaScript code
$this->registerJs($js);

?>

<style>
    .quiz-button-small {
        font-size: 12px;
        color:#a0a0a0;
        padding: 0px 2px;
        min-width: 55px;
        margin-left: 5px;
        margin-right: 5px;
    }

    .quiz-button-small:hover {
        background-color:lightskyblue;
    }

    .quiz-button {
        font-size: 14px;
        padding: 2px 5px;
        min-width: 55px;
        margin-left: 5px;
        margin-right: 5px;
    }

    .group-header .triangle {
        color: red;
        cursor: pointer;
        display: inline-block;
        transition: transform 0.3s ease-in-out;
    }

    .group-header.collapsed .triangle {
        transform: rotate(-90deg); /* Pointing right when collapsed */
    }

    .group-content {
        display: none;
        /* Initially hide the content */
    }
    .group-title{
        color: darkblue;
        font-weight: 600;
    }
</style>

<div class="quiz-index">
    <table class="table table-sm table-hover">
        <tbody>
            <?php
                $lastGroup = null;
                $index = 1;

                foreach ($quizes as $quiz):
                    $currentGroup = strstr($quiz['name'], '.', true);
                    if ($lastGroup !== $currentGroup):
                        $lastGroup = $currentGroup;
                        $groupTitle = $currentGroup ?: 'No Category';
                        // echo "<tr class='group-header'><td colspan='9'><div class='group-title'><span class='triangle'>&#9662;</span>{$groupTitle}</div></td></tr>";
                        echo "<tr class='group-header collapsed' style='color:darkblue;font-weight:600;font-style:italic;'>
                                <td colspan=3 style='width:250px;'><div class='group-title'><span class='triangle'>&#9662;</span>{$groupTitle}</div></td>
                                <td style='width:200px;'>Password</td><td style='width:120px;'>Questions</td>
                                <td style='width:35px;'>RW</td><td style='width:35px;'>BL</td><td style='width:35px;'>IP</td>
                                <td style='width:600px;'>Actions</td>
                            </tr>";
                    endif;
            ?>
                <tr>
                    <td style="color:#b0b0b0;width:25px;"><?= $index++ ?></td>
                    <td style='width:15px;'><?= Html::checkbox('active', $quiz['active'], ['value' => $quiz['id'], 'class' => 'active-radio']) ?></td>
                    <td><?= Html::a($quiz['name'], Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $quiz['id']]), ['title' => 'Show Quiz']) ?></td>
                    <td style='background-color:#f4f4f4;'><?= $quiz['password'] ?></td>
                    <td>
                        <?php
                            $id = $quiz['id'];
                            $aantalQuestion = $quizCounts[$id] ?? 0;
                            $maxQuestions = $model['no_questions'] ?? $aantalQuestion;
                            echo "{$maxQuestions} from {$aantalQuestion}";
                        ?>
                    </td>
                    <td style='background-color:#f4f4f4;'><?= $quiz['review'] ? "&#10003;" : "-" ?></td>
                    <td style='background-color:#f4f4f4;'><?= $quiz['blind'] ? "&#10003;" : "-" ?></td>
                    <td style='background-color:#f4f4f4;'><?= $quiz['ip_check'] ? "&#10003;" : "-" ?></td>
                    <td>
                        <?= Html::a('👁️ View', ['/question/list', 'quiz_id' => $quiz['id'] ], ['class' => 'btn quiz-button-small', 'title' => 'View Questions']) ?>
                        <?= Html::a('✏️ Edit', ['/quiz/update', 'id' => $quiz['id'] ], ['class' => 'btn quiz-button-small', 'title' => 'Edit Quiz']) ?>
                        <?= Html::a('❌ Delete', ['/quiz/delete', 'id' => $quiz['id'] ], [
                            'class' => 'btn quiz-button-small', 
                            'title' => 'Delete Quiz', 
                            'data-confirm' => 'Are you sure you want to delete this quiz?', 
                            'data-method' => 'post',
                        ]) ?>
                        <?= Html::a('📊 Results', ['/submission', 'quiz_id' => $quiz['id'] ], ['class' => 'btn quiz-button-small', 'title' => 'Show Results/Progress']) ?>
                        <?= Html::a('❓ Questions', ['question/index', 'quiz_id' => $quiz['id'] ], ['class' => 'btn quiz-button-small', 'title' => 'Show Questions']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<p>
    <?= Html::a('➕ New Quiz', ['create'], ['title' => 'Create New Quiz', 'class' => 'btn btn-outline-success quiz-button']) ?>
</p>