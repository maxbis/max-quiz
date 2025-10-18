<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

// Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = 'Quiz List';
// echo "<p style='color:#909090;font-size:12px;margin-top:10px;'>" . $this->title . '</p>';

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
                    nameCell.prepend('<span class="active-dot" title="Active" style="color:#28a745;font-size:1.2em;margin-right:4px;vertical-align:middle;display:inline-block;visibility:hidden;">●</span>');
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
    var content = header.nextUntil('.group-header');
    var isCollapsed = header.hasClass('collapsed');
    
    // Toggle the collapsed state
    header.toggleClass('collapsed');
    
    // Use slideToggle for smoother animation
    if (isCollapsed) {
        // Expanding
        content.slideDown(300, 'swing');
        header.find('td').css('color', 'darkblue');
    } else {
        // Collapsing
        content.slideUp(300, 'swing');
        header.find('td').css('color', 'lightgrey');
    }

    var headerIndex = $('.group-header').index(header);
    var newState = header.hasClass('collapsed');
    localStorage.setItem('groupHeader_' + headerIndex, newState);
});

$(document).ready(function() {
    // Force scrollbar to always be visible
    $('html, body').css('overflow-y', 'scroll');
    
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
    
    // Apply alternating row colors
    function applyRowColors() {
        var quizRowCount = 0;
        $('tbody tr').each(function() {
            var row = $(this);
            if (!row.hasClass('group-header')) {
                // Remove existing row classes
                row.removeClass('quiz-row-even quiz-row-odd');
                // Add appropriate class based on quiz row count
                if (quizRowCount % 2 === 0) {
                    row.addClass('quiz-row-even');
                } else {
                    row.addClass('quiz-row-odd');
                }
                quizRowCount++;
            }
        });
    }
    
    // Apply alternating row colors for all visible quiz rows
    function applyGroupedRowColors() {
        // First, remove all existing row classes
        $('tbody tr').removeClass('quiz-row-even quiz-row-odd');
        
        // Count only visible quiz rows (not group headers)
        var visibleQuizRows = $('tbody tr:not(.group-header):visible');
        var quizRowCount = 0;
        
        visibleQuizRows.each(function() {
            var row = $(this);
            if (quizRowCount % 2 === 0) {
                row.addClass('quiz-row-even');
            } else {
                row.addClass('quiz-row-odd');
            }
            quizRowCount++;
        });
    }
    
    // Apply row colors on page load
    applyGroupedRowColors();
    
    // Quiz filter functionality
    var filterTimeout;
    var filterInput = $('#quiz-filter');
    var clearBtn = $('#clear-filter');
    var allRows = $('tbody tr');
    
    // Function to perform real-time filtering
    function performFilter() {
        var filterTerm = filterInput.val().trim().toLowerCase();
        
        if (filterTerm === '') {
            // Show all rows
            allRows.show();
            clearBtn.removeClass('show');
        } else {
            // Filter rows based on quiz name
            allRows.each(function() {
                var row = $(this);
                var quizName = row.find('.quiz-name-link').text().toLowerCase();
                
                if (quizName.includes(filterTerm)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            
            clearBtn.addClass('show');
        }
        
        // Reapply row colors after filtering
        applyGroupedRowColors();
    }
    
    // Filter input event handlers
    filterInput.on('input', function() {
        var filterTerm = $(this).val().trim();
        
        // Show/hide clear button
        if (filterTerm === '') {
            clearBtn.removeClass('show');
        } else {
            clearBtn.addClass('show');
        }
        
        // Debounce the filtering
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(performFilter, 200);
    });
    
    // Clear filter button
    clearBtn.on('click', function() {
        filterInput.val('');
        clearBtn.removeClass('show');
        performFilter();
        filterInput.focus();
    });
    
    // Initial state
    if (filterInput.val().trim() === '') {
        clearBtn.removeClass('show');
    } else {
        clearBtn.addClass('show');
        performFilter();
    }
});

JS;

$this->registerJs($js); // Register the JavaScript code

// Add PDF download handler with custom dialog
$pdfUrl = Url::to(['question/pdf']);
$pdfScript = <<<JS

// Handle PDF download with filename dialog
$(document).on('click', '.pdf-download-btn', function(e) {
    e.preventDefault();
    
    var quizId = $(this).data('quiz-id');
    var quizName = $(this).data('quiz-name');
    var defaultFilename = 'quiz-' + quizId + '-' + quizName.replace(/[^a-zA-Z0-9]/g, '-') + '-' + new Date().toISOString().slice(0,10);
    
    window.showCustomDialog(
        'Generate PDF',
        'Enter filename for the PDF (without extension):',
        function() {
            var filename = $('#dialogInput').val().trim();
            if (filename !== '') {
                // Trigger PDF download
                var url = '$pdfUrl?quiz_id=' + quizId + '&filename=' + encodeURIComponent(filename);
                window.location.href = url;
            } else {
                alert('Please enter a filename');
            }
        },
        true,              // showInput = true
        defaultFilename    // defaultValue
    );
});

JS;

$this->registerJs($pdfScript);

// Handle delete quiz button click with custom dialog
$csrfParam = Yii::$app->request->csrfParam;
$deleteBaseUrl = Url::to(['quiz/delete']);
$deleteScript = <<<JS

// Handle delete quiz with custom dialog
$(document).on('click', '.delete-quiz-btn', function(e) {
    e.preventDefault();
    
    var btn = $(this);
    var quizId = btn.data('quiz-id');
    var quizName = btn.data('quiz-name');
    
    window.showCustomDialog(
        '❌ Delete Quiz',
        'Are you sure you want to delete the quiz "<strong>' + quizName + '</strong>"?<br><br><span style="color:#dc3545;">⚠️ Warning: This action cannot be undone! All associated data will be permanently deleted.</span>',
        function() {
            // Create a hidden form to submit the POST request
            var form = $('<form>', {
                'method': 'POST',
                'action': '$deleteBaseUrl' + '?id=' + quizId
            });
            
            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': '$csrfParam',
                'value': '$csrfToken'
            }));
            
            // Append to body and submit
            form.appendTo('body').submit();
        }
    );
});

JS;

$this->registerJs($deleteScript);

?>

<!-- Include the reusable custom dialog component -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>

<style>
    .main-table {
        display: none;
    }

    /* Prevent layout shift when content expands/collapses */
    body {
        overflow-x: hidden; /* Prevent horizontal scrollbar */
        overflow-y: scroll !important; /* Force vertical scrollbar to stay visible */
    }

    /* Ensure scrollbar is always visible on this page */
    html {
        overflow-y: scroll !important;
    }

    /* Fix for the filter bar moving left/right */
    .archive-filter {
        position: relative;
        left: 0;
        right: 0;
        width: 100%;
        box-sizing: border-box;
        margin-left: 0;
        margin-right: 0;
    }

    .filter-button-group {
        position: relative;
        left: 0;
        right: 0;
        margin-left: 0;
        margin-right: 0;
    }

    /* Ensure the container doesn't shift */
    .container {
        position: relative;
        left: 0;
        right: 0;
    }

    /* Fix table column widths to prevent horizontal shifting */
    table {
        table-layout: fixed;
        width: 100%;
    }

    /* Set specific column widths to prevent shifting */
    table td:first-child {
        width: 20px; /* Bullet point column */
    }

    table td:nth-child(2) {
        width: 20px; /* Checkbox column */
    }

    table td:nth-child(3) {
        width: 250px; /* Quiz name column */
    }

    /* Ensure checkboxes and dots don't cause width changes */
    .active-radio {
        width: 16px;
        height: 16px;
        margin: 0;
    }

    .active-dot {
        width: 12px;
        height: 12px;
        display: inline-block;
        text-align: center;
    }


    .group-header .triangle {
        color: #007bff;
        cursor: pointer;
        display: inline-block;
        transition: all 0.3s ease-in-out;
        font-size: 1.2em;
        margin-right: 8px;
        padding: 2px 4px;
        border-radius: 3px;
        background-color: rgba(0, 123, 255, 0.1);
        user-select: none;
    }

    .group-header .triangle:hover {
        color: #0056b3;
        background-color: rgba(0, 123, 255, 0.2);
        transform: scale(1.1);
    }

    .group-header.collapsed .triangle {
        transform: rotate(-90deg);
        /* Pointing right when collapsed */
    }

    .group-header.collapsed .triangle:hover {
        transform: rotate(-90deg) scale(1.1);
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
        vertical-align: middle;
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
        vertical-align: middle;
    }
    
    .active-radio {
        vertical-align: middle;
        margin: 0;
    }
    
    .active-dot {
        display: inline-block;
        line-height: 1;
        margin-bottom: 12px;
        position: relative;
    }
    .group-header {
        background: #e3e8f0 !important;
        font-weight: 600 !important;
        font-size: 1.08em;
        border-bottom: 2px solid #d0d7e5;
    }

    /* Alternating row colors for quiz rows */
    tbody tr.quiz-row-even {
        background-color: #f8f9fa;
    }
    
    tbody tr.quiz-row-odd {
        background-color: #ffffff;
    }

    /* Active quiz highlighting - override alternating colors */
    tbody tr[style*="background-color:#e6ffe6"] {
        background-color: #e6ffe6 !important;
    }

    tr:hover {
        background: #e6f7ff !important;
    }
    
    /* Ensure group headers don't get hover effects */
    .group-header:hover {
        background: #e3e8f0 !important;
    }

    
    .dropdown-menu {
        min-width: 150px;
    }
    
    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
    }
    
    /* Archive filter styling */
    .archive-filter {
        margin-bottom: 20px;
        margin-top: 20px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .filter-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .filter-right {
        display: flex;
        align-items: center;
    }
    
    .archive-filter label {
        margin-right: 15px;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
    }
    
    /* Quiz filter styling */
    .quiz-filter-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .quiz-filter-container label {
        margin-right: 8px;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
        white-space: nowrap;
    }
    
    .quiz-filter-input-group {
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }
    
    .quiz-filter-input {
        width: 250px;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .quiz-filter-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }
    
    .clear-filter-btn {
        padding: 8px 12px;
        border: 1px solid #ced4da;
        background: white;
        color: #6c757d;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        min-width: 40px;
        display: none;
    }
    
    .clear-filter-btn:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
        color: #495057;
    }
    
    .clear-filter-btn.show {
        display: inline-block;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .archive-filter {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-left,
        .filter-right {
            justify-content: center;
        }
        
        .quiz-filter-input {
            width: 200px;
        }
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
            <div class="filter-left">
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
            <div class="filter-right">
                <div class="quiz-filter-container">
                    <label for="quiz-filter">Filter:</label>
                    <div class="quiz-filter-input-group">
                        <input type="text" 
                               id="quiz-filter" 
                               class="form-control quiz-filter-input" 
                               placeholder="Filter quiz names..." 
                               autocomplete="off">
                        <button type="button" id="clear-filter" class="btn btn-outline-secondary clear-filter-btn" title="Clear filter">
                            ✕
                        </button>
                    </div>
                </div>
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
                                    <div class='group-title'><span class='triangle' title='Click to expand/collapse'>▼</span>&nbsp;{$groupTitle}</div>
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
                        <td style="color:#e0e0e0;width:15px;vertical-align:middle;">•</td>
                        <td style='width:15px; vertical-align: middle;'>
                            <?php 
                            $isArchived = isset($quiz['archived']) ? $quiz['archived'] : false;
                            $checkboxOptions = ['value' => $quiz['id'], 'class' => 'active-radio'];
                            if ($isArchived) {
                                $checkboxOptions['disabled'] = true;
                            }
                            ?>
                            <?= Html::checkbox('active', $quiz['active'], $checkboxOptions) ?>
                        </td>
                        <td class="quiz-name-cell" style="width:250px; vertical-align: middle;<?= $quiz['active'] ? 'font-weight:bold;' : '' ?>">
                            <span class="active-dot" title="Active" style="color:#28a745;font-size:1.2em;margin-right:4px;vertical-align:middle;<?= $quiz['active'] ? '' : 'visibility:hidden;' ?>">●</span>
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
                                    <?= Html::a('🏷️ Labels/Sort', ['/quiz/edit-labels', 'id' => $quiz['id']], ['class' => 'dropdown-item', 'title' => 'Edit Question Labels']) ?>
                                    <?= Html::a('📄 PDF', '#', [
                                        'class' => 'dropdown-item pdf-download-btn',
                                        'title' => 'Generate PDF',
                                        'data-quiz-id' => $quiz['id'],
                                        'data-quiz-name' => $quiz['name']
                                    ]) ?>
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
                                    <?= Html::a('❌ Delete', '#', [
                                        'class' => 'dropdown-item text-danger delete-quiz-btn',
                                        'title' => 'Delete Quiz',
                                        'data-quiz-id' => $quiz['id'],
                                        'data-quiz-name' => $quiz['name'],
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