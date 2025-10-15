<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quiz List';
echo "<p style='color:#909090;font-size:16px;'>" . $this->title . '</p>';

$csrfToken = Yii::$app->request->getCsrfToken();
$id = Yii::$app->request->get('id');

$apiUrl = Url::toRoute(['/quiz-question/active']);
$submissionBaseUrl = Url::toRoute(['/submission']);
$questionIndexBaseUrl = Url::toRoute(['/question/index']);

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
            var row = $('#quiz-row-' + id);
            var nameCell = row.find('.quiz-name-cell');
            if (active) {
                row.css('background-color', '#e6ffe6');
                if (nameCell.find('.active-dot').length === 0) {
                    nameCell.prepend('<span class="active-dot" title="Active" style="color:#28a745;font-size:1.2em;margin-right:4px;">●</span>');
                }
                nameCell.css('font-weight', 'bold');
            } else {
                row.css('background-color', '');
                nameCell.find('.active-dot').remove();
                nameCell.css('font-weight', '');
            }
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
    
    // Update the quiz name link dynamically
    var row = $('#quiz-row-' + quizId);
    var link = row.find('.quiz-name-link');
    if (isActive) {
        link.attr('href', '$submissionBaseUrl' + '?quiz_id=' + quizId);
        link.attr('title', 'Show Results');
    } else {
        link.attr('href', '$questionIndexBaseUrl' + '?quiz_id=' + quizId);
        link.attr('title', 'Show Questions');
    }
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
    $('.main-table').fadeIn('fast');
});

JS;

$this->registerJs($js); // Register the JavaScript code

?>

<style>
    .main-table {
        display: none;
    }

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


    tr:hover .grey-column {
        background-color: #f0f0f9;
    }

    tr:hover .highlight-column {
        color: #333;
    }

    .quiz-name-cell {
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .group-header {
        background: #e3e8f0 !important;
        font-weight: 600 !important;
        font-size: 1.08em;
        border-bottom: 2px solid #d0d7e5;
    }

    tr:hover {
        background: #e6f7ff;
    }

    
    .dropdown-menu {
        min-width: 150px;
    }
    
    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
    }
    
    /* Fix vertical alignment of action buttons */
    .quiz-button-small {
        vertical-align: top !important;
        display: inline-flex !important;
        align-items: center !important;
        line-height: 1 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        font-size: 12px !important;
        padding: 2px 4px !important;
    }
    
    .btn-group {
        vertical-align: top !important;
        display: inline-block !important;
    }
    
    .btn-group .quiz-button-small {
        vertical-align: top !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
</style

<body>
    <div class="quiz-index">
        <table class="main-table">
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
                                <td style='width:15px;'></td>
                                <td style='width:15px;'></td>
                                <td style='width:250px;'>
                                    <div class='group-title'><span class='triangle'>&#9662;</span>&nbsp;{$groupTitle}</div>
                                </td>
                                <td style='width:200px;color:lightgrey'>Password</td>
                                <td style='width:120px;color:lightgrey'>Questions</td>
                                <td style='width:100px;color:lightgrey'>Taken</td>
                                <td title='Review Quiz' style='width:35px;color:lightgrey'>RW</td>
                                <td title='Blind Quiz' style='width:35px;color:lightgrey'>BL</td>
                                <td title='IP Check' style='width:35px;color:lightgrey'>IP</td>
                                 <td title='Random' style='width:35px;color:lightgrey'>Rd</td>
                                <td style='width:300px;color:lightgrey'>Actions</td>
                            </tr>";
                    endif;
                    ?>
                    <tr id="quiz-row-<?= $quiz['id'] ?>"<?= $quiz['active'] ? " style='background-color:#e6ffe6;'" : '' ?>>
                        <td style="color:#e0e0e0;width:15px;">•</td>
                        <td style='width:15px;'>
                            <?= Html::checkbox('active', $quiz['active'], ['value' => $quiz['id'], 'class' => 'active-radio']) ?>
                        </td>
                        <td class="quiz-name-cell" style="width:250px;<?= $quiz['active'] ? 'font-weight:bold;' : '' ?>">
                            <?php if ($quiz['active']) echo "<span class='active-dot' title='Active' style='color:#28a745;font-size:1.2em;margin-right:4px;'>●</span>"; ?>
                            <?php 
                                $url = $quiz['active'] 
                                    ? Yii::$app->urlManager->createUrl(['submission', 'quiz_id' => $quiz['id']]) 
                                    : Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $quiz['id']]);
                                $title = $quiz['active'] ? 'Show Results' : 'Show Questions';
                            ?>
                            <?= Html::a($quiz['name'], $url, ['title' => $title, 'class' => 'quiz-name-link']) ?>
                        </td>
                        <td class="highlight-column" _style='background-color:#f8f8f8;color:#d0d0e0;'>
                            <?= $quiz['password'] ?>
                        </td>
                        <td>
                            <?php
                            $id = $quiz['id'];
                            $aantalQuestion = $quizCounts[$id] ?? 0;
                            $maxQuestions = $quiz['no_questions'] ?? $aantalQuestion;
                            echo "{$maxQuestions} from {$aantalQuestion}";
                            ?>
                        </td>
                        <td>
                            <?php
                            $takenCount = $quizTakenCounts[$quiz['id']] ?? 0;
                            echo $takenCount;
                            ?>
                        </td>
                        <td class="grey-column">
                            <?= $quiz['review'] ? "&#10003;" : "-" ?>
                        </td>
                        <td class="grey-column">
                            <?= $quiz['blind'] ? "&#10003;" : "-" ?>
                        </td>
                        <td class="grey-column">
                            <?= $quiz['ip_check'] ? "&#10003;" : "-" ?>
                        </td>
                        <td class="grey-column">
                            <?= $quiz['random'] ? "&#10003;" : "-" ?>
                        </td>
                        <td>
                            <?= Html::a('❓ Questions', ['question/index', 'quiz_id' => $quiz['id']], ['class' => 'btn quiz-button-small', 'title' => 'Show Questions']) ?>
                            <?= Html::a('✏️ Edit', ['/quiz/update', 'id' => $quiz['id']], ['class' => 'btn quiz-button-small', 'title' => 'Edit Quiz']) ?>
                            <div class="btn-group" style="display: inline-block;">
                                <button type="button" class="btn quiz-button-small dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ⋮ More
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <?= Html::a('👁️ View', ['/question/list', 'quiz_id' => $quiz['id']], ['class' => 'dropdown-item', 'title' => 'View Questions']) ?>
                                    <?= Html::a('📊 Results', ['/submission', 'quiz_id' => $quiz['id']], ['class' => 'dropdown-item', 'title' => 'Show Results/Progress']) ?>
                                    <?= Html::a('🏷️ Labels', ['/quiz/edit-labels', 'id' => $quiz['id']], ['class' => 'dropdown-item', 'title' => 'Edit Question Labels']) ?>
                                    <?= Html::a('📄 PDF', ['/question/pdf', 'quiz_id' => $quiz['id']], ['class' => 'dropdown-item', 'title' => 'Generate PDF']) ?>
                                    <div class="dropdown-divider"></div>
                                    <?= Html::a('❌ Delete', ['/quiz/delete', 'id' => $quiz['id']], [
                                        'class' => 'dropdown-item text-danger',
                                        'title' => 'Delete Quiz',
                                        'data-confirm' => 'Are you sure you want to delete this quiz?',
                                        'data-method' => 'post',
                                    ]) ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <p style='margin-top:30px;'>
        <?= Html::a('➕ New Quiz', ['create'], ['title' => 'Create New Quiz', 'class' => 'btn btn-outline-success quiz-button']) ?>
        &nbsp;&nbsp;&nbsp;
        <?= Html::a('Disable All', ['index', 'reset' => 1], ['title' => 'Disbale all quizes', 'class' => 'btn btn-outline-success quiz-button']) ?>
    </p>
</body>