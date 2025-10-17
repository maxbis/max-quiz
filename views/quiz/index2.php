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
$showFilter = $showFilter ?? 'active';
$id = Yii::$app->request->get('id');

$apiUrl = Url::toRoute(['/quiz-question/active']);
$submissionBaseUrl = Url::toRoute(['/submission']);
$questionIndexBaseUrl = Url::toRoute(['/question/index']);

$js = <<<JS

function updateActiveStatus(id, active) {
    $.ajax({
        url: '$apiUrl',
        method: 'POST',
        data: {  _csrf: '$csrfToken',
                id: id,
                active: active ? 1 : 0
        },
        success: function(response) {
            var row = $('#quiz-row-' + id);
            var nameCell = row.find('.quiz-name-cell');
            if (active) {
                row.css('background-color', '#e6ffe6');
                // Use visibility instead of adding/removing to prevent layout shift
                var dot = nameCell.find('.active-dot');
                if (dot.length === 0) {
                    nameCell.prepend('<span class="active-dot" title="Active" style="color:#28a745;font-size:1.2em;margin-right:4px;visibility:hidden;">●</span>');
                    dot = nameCell.find('.active-dot');
                }
                dot.css('visibility', 'visible');
                nameCell.css('font-weight', 'bold');
            } else {
                row.css('background-color', '');
                nameCell.find('.active-dot').css('visibility', 'hidden');
                nameCell.css('font-weight', '');
            }
        },
        error: function(xhr, status, error) {
        }
    });
}

// Handle the change event of the checkboxes for active status
$(document).ready(function() {
    $('input[name="active"]').on('change', function() {
        var quizId = $(this).val();
        var isActive = $(this).prop('checked');
        var isDisabled = $(this).prop('disabled');
        if (isDisabled) { return; }
        
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
});

$(document).on('click', '.group-header', function() {
    var header = $(this);
    header.toggleClass('collapsed');
    header.nextUntil('.group-header').fadeToggle(400, 'swing'); // This will show/hide the rows until the next group header

    var headerIndex = $('.group-header').index(header);
    var isCollapsed = header.hasClass('collapsed');
    localStorage.setItem('groupHeader_' + headerIndex, isCollapsed);
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
    
    // Enhanced filter button interactions
    $('.filter-button-group .btn').on('click', function() {
        // Add a subtle click animation
        $(this).addClass('btn-clicked');
        setTimeout(() => {
            $(this).removeClass('btn-clicked');
        }, 150);
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
    
    /* Archive filter styling */
    .archive-filter {
        margin-bottom: 20px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .archive-filter label {
        margin-right: 15px;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
    }
    
    .filter-button-group {
        display: inline-flex;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .filter-button-group .btn {
        border-radius: 0;
        border-right: 1px solid rgba(255,255,255,0.2);
        position: relative;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 13px;
        padding: 8px 16px;
    }
    
    .filter-button-group .btn:first-child {
        border-top-left-radius: 6px;
        border-bottom-left-radius: 6px;
    }
    
    .filter-button-group .btn:last-child {
        border-top-right-radius: 6px;
        border-bottom-right-radius: 6px;
        border-right: none;
    }
    
    .filter-button-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    
    .filter-button-group .btn.active {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #0056b3;
        color: white;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
    }
    
    .filter-button-group .btn:not(.active) {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-color: #ced4da;
        color: #6c757d;
    }
    
    .filter-button-group .btn:not(.active):hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        color: #495057;
    }
    
    .filter-button-group .btn.btn-clicked {
        transform: translateY(0) scale(0.98);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Archived quiz styling */
    .archived-quiz {
        opacity: 0.6;
        background-color: #f5f5f5 !important;
    }
    
    .archived-quiz:hover {
        opacity: 0.8;
        background-color: #ebebeb !important;
    }
    
    .archived-badge {
        display: inline-block;
        background-color: #6c757d;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-left: 5px;
        vertical-align: middle;
    }
</style>

<body>
    <div class="quiz-index">
        <!-- Archive Filter -->
        <div class="archive-filter">
            <label>Show:</label>
            <div class="filter-button-group" role="group" aria-label="Quiz filter options">
                <?= Html::a('Active Quizzes', ['index', 'show' => 'active'], [
                    'class' => 'btn ' . ($showFilter === 'active' ? 'active' : ''),
                    'role' => 'button',
                    'aria-pressed' => $showFilter === 'active' ? 'true' : 'false',
                    'title' => 'Show only active quizzes'
                ]) ?>
                <?= Html::a('Archived Quizzes', ['index', 'show' => 'archived'], [
                    'class' => 'btn ' . ($showFilter === 'archived' ? 'active' : ''),
                    'role' => 'button',
                    'aria-pressed' => $showFilter === 'archived' ? 'true' : 'false',
                    'title' => 'Show only archived quizzes'
                ]) ?>
                <?= Html::a('All Quizzes', ['index', 'show' => 'all'], [
                    'class' => 'btn ' . ($showFilter === 'all' ? 'active' : ''),
                    'role' => 'button',
                    'aria-pressed' => $showFilter === 'all' ? 'true' : 'false',
                    'title' => 'Show all quizzes'
                ]) ?>
            </div>
        </div>
        
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
                    <tr id="quiz-row-<?= $quiz['id'] ?>" class="<?= (isset($quiz['archived']) && $quiz['archived']) ? 'archived-quiz' : '' ?>"<?= $quiz['active'] ? " style='background-color:#e6ffe6;'" : '' ?>>
                        <td style="color:#e0e0e0;width:15px;">•</td>
                        <td style='width:15px;'>
                            <?php 
                            $isArchived = isset($quiz['archived']) ? $quiz['archived'] : false;
                            $checkboxOptions = ['value' => $quiz['id'], 'class' => 'active-radio'];
                            if ($isArchived) {
                                $checkboxOptions['disabled'] = true;
                            }
                            ?>
                            <?= Html::checkbox('active', $quiz['active'], $checkboxOptions) ?>
                        </td>
                        <td class="quiz-name-cell" style="width:250px;<?= $quiz['active'] ? 'font-weight:bold;' : '' ?>">
                            <span class="active-dot" title="Active" style="color:#28a745;font-size:1.2em;margin-right:4px;<?= $quiz['active'] ? '' : 'visibility:hidden;' ?>">●</span>
                            <?php 
                                $url = $quiz['active'] 
                                    ? Yii::$app->urlManager->createUrl(['submission', 'quiz_id' => $quiz['id']]) 
                                    : Yii::$app->urlManager->createUrl(['question/index', 'quiz_id' => $quiz['id']]);
                                $title = $quiz['active'] ? 'Show Results' : 'Show Questions';
                            ?>
                            <?= Html::a($quiz['name'], $url, ['title' => $title, 'class' => 'quiz-name-link']) ?>
                            <?php if (isset($quiz['archived']) && $quiz['archived']) echo "<span class='archived-badge'>ARCHIVED</span>"; ?>
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
                                    <?php if (isset($quiz['archived']) && $quiz['archived']): ?>
                                        <?= Html::a('📤 Restore', ['/quiz/toggle-archive', 'id' => $quiz['id']], [
                                            'class' => 'dropdown-item text-success',
                                            'title' => 'Restore Quiz from Archive',
                                            'data-method' => 'post',
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('📦 Archive', ['/quiz/toggle-archive', 'id' => $quiz['id']], [
                                            'class' => 'dropdown-item text-success',
                                            'title' => 'Archive Quiz',
                                            'data-method' => 'post',
                                        ]) ?>
                                    <?php endif; ?>
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