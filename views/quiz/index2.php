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
    header.nextUntil('.group-header').fadeToggle(400, 'swing'); // This will show/hide the rows until the next group header

    var headerIndex = $('.group-header').index(header);
    var isCollapsed = header.hasClass('collapsed');
    localStorage.setItem('groupHeader_' + headerIndex, isCollapsed);
    console.log('groupHeader_' + headerIndex, isCollapsed);
    if ( isCollapsed ) {
        header.find('td').css('color', 'lightgrey');
    } else {
        header.find('td').css('color', 'darkblue');
    }
});

// $(document).ready(function() {
//     $('.group-header.collapsed').nextUntil('.group-header').hide(); // This hides all rows that follow a '.group-header.collapsed' until the next '.group-header'
// });
$(document).ready(function() {
    // Collapse all by default
    $('.group-header.collapsed').nextUntil('.group-header').hide();

    // Check localStorage and apply saved states
    $('.group-header').each(function(index) {
        var isCollapsed = localStorage.getItem('groupHeader_' + index);
        // If there's a saved state, apply it
        if (isCollapsed !== null) {
            if (isCollapsed === 'true') {
                $(this).addClass('collapsed').nextUntil('.group-header').hide();
                $(this).find('td').css('color', 'lightgrey');
            } else {
                $(this).removeClass('collapsed').nextUntil('.group-header').show();
                $(this).find('td').css('color', 'darkblue');
            }
        }
    });
});

JS;

// Register the JavaScript code
$this->registerJs($js);

?>

<style>
    .quiz-button-small {
        font-size: 12px;
        color: #a0a0a0;
        padding: 0px 2px;
        min-width: 55px;
        margin-left: 5px;
        margin-right: 5px;
    }

    .quiz-button-small:hover {
        background-color: lightskyblue;
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
        transform: rotate(-90deg);
        /* Pointing right when collapsed */
    }

    .group-header td {
        transition: color 0.5s ease;
        /* Transition effect for color change */
    }

    .group-content {
        display: none;
        /* Initially hide the content */
    }

    .group-title {
        color: darkblue;
        font-weight: 600;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border-bottom: 1px solid #ddd;
        padding: 4px;
        text-align: left;
    }

    .highlight-column {
        color: #e8e8e8;
        transition: color 0.8s ease;
    }

    .grey-column {
        background-color: #f8f8f8;
    }

    tr:hover {
        cursor: pointer;
        background-color: #f0f0f9;
    }

    tr:hover .grey-column {
        background-color: #f0f0f9;
    }

    tr:hover .highlight-column {
        color: #333;
    }
</style>

<div class="quiz-index">
    <table class="">
        <tbody>
            <?php
            $lastGroup = null;
            $index = 1;

            foreach ($quizes as $quiz):
                $currentGroup = strstr($quiz['name'], '.', true);
                if ($lastGroup !== $currentGroup):
                    $lastGroup = $currentGroup;
                    $groupTitle = $currentGroup ?: 'No Category';
                    echo "<tr class='group-header collapsed' style='background-color:#f0f0f9;color:darkblue;font-weight:350;font-style:italic;'>
                                <td colspan=3 style='width:250px;'><div class='group-title'><span class='triangle'>&#9662;</span>&nbsp;{$groupTitle}</div></td>
                                <td style='width:200px;color:lightgrey'>Password</td>
                                <td style='width:120px;color:lightgrey'>Questions</td>
                                <td title='Review Quiz' style='width:35px;color:lightgrey'>RW</td>
                                <td title='Blind Quiz' style='width:35px;color:lightgrey'>BL</td>
                                <td title='IP Check' style='width:35px;color:lightgrey'>IP</td>
                                <td style='width:600px;color:lightgrey'>Actions</td>
                            </tr>";
                endif;
                ?>
                <tr>
                    <td style="color:#e0e0e0;width:15px;">•</td>
                    <td style='width:15px;'>
                        <?= Html::checkbox('active', $quiz['active'], ['value' => $quiz['id'], 'class' => 'active-radio']) ?>
                    </td>
                    <td>
                        <?= Html::a($quiz['name'], Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $quiz['id']]), ['title' => 'Show Quiz']) ?>
                    </td>
                    <td class="highlight-column" _style='background-color:#f8f8f8;color:#d0d0e0;'>
                        <?= $quiz['password'] ?>
                    </td>
                    <td>
                        <?php
                        $id = $quiz['id'];
                        $aantalQuestion = $quizCounts[$id] ?? 0;
                        $maxQuestions = $model['no_questions'] ?? $aantalQuestion;
                        echo "{$maxQuestions} from {$aantalQuestion}";
                        ?>
                    </td>
                    <td class="grey-column" _style='background-color:#f8f8f8;'>
                        <?= $quiz['review'] ? "&#10003;" : "-" ?>
                    </td>
                    <td class="grey-column" _style='background-color:#f4f4f4;'>
                        <?= $quiz['blind'] ? "&#10003;" : "-" ?>
                    </td>
                    <td class="grey-column" _style='background-color:#f4f4f4;'>
                        <?= $quiz['ip_check'] ? "&#10003;" : "-" ?>
                    </td>
                    <td>
                        <?= Html::a('✏️ Edit', ['/quiz/update', 'id' => $quiz['id']], ['class' => 'btn quiz-button-small', 'title' => 'Edit Quiz']) ?>
                        <?= Html::a('👁️ View', ['/question/list', 'quiz_id' => $quiz['id']], ['class' => 'btn quiz-button-small', 'title' => 'View Questions']) ?>
                        <?= Html::a('❌ Delete', ['/quiz/delete', 'id' => $quiz['id']], [
                            'class' => 'btn quiz-button-small',
                            'title' => 'Delete Quiz',
                            'data-confirm' => 'Are you sure you want to delete this quiz?',
                            'data-method' => 'post',
                        ]) ?>
                        <?= Html::a('❓ Questions', ['question/index', 'quiz_id' => $quiz['id']], ['class' => 'btn quiz-button-small', 'title' => 'Show Questions']) ?>
                        <?= Html::a('📊 Results', ['/submission', 'quiz_id' => $quiz['id']], ['class' => 'btn quiz-button-small', 'title' => 'Show Results/Progress']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<p style='margin-top:30px;'>
    <?= Html::a('➕ New Quiz', ['create'], ['title' => 'Create New Quiz', 'class' => 'btn btn-outline-success quiz-button']) ?>
</p>