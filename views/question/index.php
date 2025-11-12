<?php

use app\models\Question;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

// Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);

// $this->title = 'Quiz Details';
// $this->params['breadcrumbs'][] = $this->title;
// echo "<p style='color:#909090;font-size:16px;'>" . $this->title . '</p>';

$currentRoute = Yii::$app->controller->getRoute();
$params = Yii::$app->request->getQueryParams();

# headerStatus is the view-status: all, only-checked, onlyunchecked
$headerStatus = ['A', 'X', '&#x2713'];
# When navigating to the next status, determine the next URL
# the show parameter circles through 1,0,-1
$nextShow = $show - 1;
if ($nextShow < -1)
    $nextShow = 1;
$params['show'] = $nextShow;
$clickedOnHeader = Url::toRoute(array_merge([$currentRoute], $params));

$csrfToken = Yii::$app->request->getCsrfToken();
$apiUrl = Url::toRoute(['/quiz-question/connect']);
$id = Yii::$app->request->get('id');

// Expose total assigned questions to JS for live updates
// No extra JS variables needed here

$script = <<<JS
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
            // Update active count (configured number remains constant)
            $('#countActive').text(response.result.count);
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

// Yii places the function outside of the scope of the HTML page, therefor we attach it to the window object
$script = <<<JS
    window.checkAllCheckboxes = function checkAllCheckboxes(thisValue) {
        if (confirm("This can not be undone, proceed?")) {
            $('input[type="checkbox"]').each(function() {
                $(this).prop('checked', thisValue).trigger('change');
            });
        }
    }
JS;
$this->registerJs($script);

$script = <<<JS
    window.headerCheckbox = function headerCheckbox(show) {
        console.log('$clickedOnHeader');
        window.location.href='$clickedOnHeader';
    }
JS;
$this->registerJs($script);

// Make entire table rows clickable
$script = <<<JS
    $(document).ready(function() {
        $('.grid-view tbody tr').click(function(e) {
            // Don't trigger if clicking on checkbox, delete button, view icon, or other interactive elements
            if (e.target.type === 'checkbox' || 
                $(e.target).hasClass('status-checkbox') || 
                $(e.target).hasClass('delete-question-btn') ||
                $(e.target).closest('a[href*="delete"]').length > 0 ||
                $(e.target).closest('a[href*="view"]').length > 0) {
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

// Handle test quiz button click with custom dialog
$quizId = $quiz['id'];
$quizActive = $quiz['active'];
$activateApiUrl = Url::to(['quiz-question/active']);

$script = <<<JS
    $(document).ready(function() {
        // Make quizIsActive global so it can be updated by the toggle dot
        window.quizIsActive = {$quizActive};
        
        $('#test-quiz-button').click(function() {
            var quizIsActive = window.quizIsActive;
            var quizId = {$quizId};
            
            // Prepare message based on quiz active status
            var message = 'Start quiz with test data (First Name: Test, Last Name: Test, Student Number: 99999, Class: 99)?';
            if (!quizIsActive) {
                message += '<br><br><strong>Note:</strong> This quiz is currently inactive and will be set to ACTIVE.';
            }
            
            window.showCustomDialog(
                '🧪 Test Quiz',
                message,
                function() {
                    // If quiz is not active, activate it first
                    if (!quizIsActive) {
                        // Show loading message
                        $('#modalMessage').html('Activating quiz...<br><small>Please wait</small>');
                        $('#modalOverlay').show();
                        
                        // Make AJAX call to activate the quiz
                        $.ajax({
                            url: '$activateApiUrl',
                            type: 'POST',
                            data: {
                                _csrf: '$csrfToken',
                                id: quizId,
                                active: 1
                            },
                            success: function(response) {
                                // Update the UI to show quiz is now active
                                window.quizIsActive = 1;
                                quizIsActive = true;
                                
                                // Update the dot appearance as well
                                $('#quiz-status-dot').removeClass('dot-red').addClass('dot-green');
                                $('#quiz-status-dot').attr('title', 'Quiz is active - Click to deactivate');
                                $('#quiz-status-dot').data('active', 1);
                                
                                // Now start the test quiz
                                $('#modalMessage').html('Starting test quiz in new tab...<br><small>This may take a few seconds</small>');
                                
                                setTimeout(function() {
                                    $('#test-quiz-form').submit();
                                    
                                    setTimeout(function() {
                                        $('#modalOverlay').hide();
                                        $('#modalMessage').text('Please wait...');
                                        
                                        // Refresh the page to show updated active status
                                        location.reload();
                                    }, 1500);
                                }, 300);
                            },
                            error: function(xhr, status, error) {
                                $('#modalOverlay').hide();
                                alert('Error activating quiz: ' + error);
                            }
                        });
                    } else {
                        // Quiz is already active, just start it
                        $('#modalMessage').html('Starting test quiz in new tab...<br><small>This may take a few seconds</small>');
                        $('#modalOverlay').show();
                        
                        setTimeout(function() {
                            $('#test-quiz-form').submit();
                            
                            setTimeout(function() {
                                $('#modalOverlay').hide();
                                $('#modalMessage').text('Please wait...');
                            }, 1500);
                        }, 300);
                    }
                }
            );
        });
    });
JS;
$this->registerJs($script);

# $show = Yii::$app->request->get('show', 1);
$QuestionLabelText = 'Active Quiz Questions';
if ($show == 0) {
    $QuestionLabelText = 'Inactive Questions';
} elseif ($show == -1) {
    $QuestionLabelText = 'All Questions';
}
?>

<style>
    .multiline-tooltip::after {
        content: attr(data-tooltip);
        display: none;
        position: fixed;
        /* fixed = relative to viewport */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        /* centers horizontally and vertically */
        z-index: 9999;
        background: #dbe6ff;
        border: 1px solid #ccc;
        padding: 10px 15px;
        font-family: monospace;
        white-space: pre-wrap;
        min-width: 600px;
        max-width: 900px;
        min-height: 80px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        border-radius: 6px;
    }

    .multiline-tooltip:hover::after {
        display: block;
    }

    .dot {
        height: 16px;
        width: 16px;
        border-radius: 50%;
        display: inline-block;
        margin-top: 6px;
        margin-right: 5px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dot:hover {
        transform: scale(1.3);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.8);
    }

    .dot-red {
        background-color: salmon;
    }

    .dot-green {
        background-color: lightgreen;
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
</style>

<!-- This is the busy overlay, show as more than one question is updated via AJAX or when starting test quiz -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-dialog">
        <div class="loader"></div>
        <p id="modalMessage">Please wait... </p>
    </div>
</div>

<div class="quiz-card"
    style="max-width:750px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                $statusClass = $quiz['active'] == 1 ? 'dot-green' : 'dot-red';
                $statusHelp = $quiz['active'] == 1 ? 'Quiz is active - Click to deactivate' : 'Quiz is inactive - Click to activate';
                ?>
                <h2
                    style="font-size:2.1em; font-weight: bold; margin-bottom: 0.2em; display: flex; align-items: center;">
                    <span id="quiz-status-dot" title="<?= $statusHelp ?>" class="dot <?= $statusClass ?>"
                        data-quiz-id="<?= $quiz['id'] ?>" data-active="<?= $quiz['active'] ?>"
                        style="margin-right:10px;"></span>
                    <span><?= $quiz['name'] ?></span>
                </h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div
                    style="display: flex; gap: 30px; align-items: center; background: #f8f9fa; border-radius: 6px; padding: 10px 18px; margin: 12px 0 18px 0; box-shadow: 0 1px 4px rgba(0,0,0,0.03); font-size: 1.1em;">
                    <span style="display: flex; align-items: center; color: #888;">
                        <span style="font-size:1.3em; margin-right: 7px;">🔒</span>
                        <span style="font-weight: 500;">Password:</span> <span
                            style="margin-left: 5px; color: #404080; font-family: monospace;">
                            <?= Html::encode($quiz['password']) ?> </span>
                    </span>
                    <span style="display: flex; align-items: center; color: #888;">
                        <span style="font-size:1.3em; margin-right: 7px;">❓</span>
                        <span style="font-weight: 500;">Questions:</span>
                        <span style="margin-left: 5px; color: #404080;">
                            <span id="countActive"><?= count($questionIds); ?></span>
                            <span style="color:#9aa0a6;">
                                (quiz max is <span id="countConfigured"><?= (int)($quiz['no_questions'] ?? count($questionIds)) ?></span>)
                            </span>
                        </span>
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex align-items-end">
                <?= Html::a('✏️ Edit', ['quiz/update', 'id' => $quiz['id']], ['class' => 'btn btn-outline-primary quiz-button'], ) ?>
                <?php
                $url = Yii::$app->urlManager->createUrl(['/question/list', 'quiz_id' => $quiz['id']]);
                echo Html::a('👁️ View', $url, ['title' => 'View Questions', 'class' => 'btn btn-outline-success quiz-button',]);
                ?>
                <?= Html::a(
                    '🏷️ Sort',
                    ['quiz/edit-labels', 'id' => $quiz['id']],
                    [
                        'class' => 'btn btn-outline-success quiz-button',
                        'title' => 'Edit Question Labels',
                    ]
                ); ?>
                <?= Html::a(
                    '📋 Copy',
                    '#',
                    [
                        'class' => 'btn btn-outline-danger quiz-button copy-quiz-btn',
                        'id' => 'copy-quiz-button',
                        'data-quiz-id' => $quiz['id'],
                        'data-quiz-name' => $quiz['name'],
                    ],
                ); ?>
                <?= Html::a(
                    '📄 PDF',
                    '#',
                    [
                        'class' => 'btn btn-outline-secondary quiz-button pdf-download-btn',
                        'title' => 'Generate PDF with all questions for this quiz',
                        'data-quiz-id' => $quiz['id'],
                        'data-quiz-name' => $quiz['name']
                    ]
                ); ?>
                <?= Html::button(
                    '🧪 Test',
                    [
                        'class' => 'btn btn-outline-info quiz-button',
                        'title' => 'Test Quiz',
                        'id' => 'test-quiz-button',
                    ]
                ); ?>
                <?php
                $url = Yii::$app->urlManager->createUrl(['/submission', 'quiz_id' => $quiz['id']]);
                echo Html::a('📊 Results', $url, [
                    'title' => 'Show Results/Progress',
                    'class' => 'btn btn-outline-dark quiz-button',
                ]);
                ?>

            </div>
        </div>
    </div>
</div>

<!-- Hidden form for test quiz submission -->
<form id="test-quiz-form" action="<?= Yii\helpers\Url::to(['/submission/start']) ?>" method="POST" target="_blank"
    style="display: none;">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
    <input type="hidden" name="first_name" value="Test">
    <input type="hidden" name="last_name" value="Test">
    <input type="hidden" name="student_nr" value="99999">
    <input type="hidden" name="class" value="99">
    <input type="hidden" name="password" value="<?= Html::encode($quiz['password']) ?>">
</form>


<div class="question-index">

    <div class="question-tabs" style="margin-bottom: 20px;">
        <?php
        $tabItems = [
            1 => [
                'label' => 'Active Quiz Questions',
                'show' => 1,
                'desc' => 'Questions currently assigned and active',
                'tooltip' => 'Show only active questions assigned to this quiz.'
            ],
            0 => [
                'label' => 'Inactive Questions',
                'show' => 0,
                'desc' => 'Assigned but set as inactive',
                'tooltip' => 'Show questions assigned to this quiz but marked inactive.'
            ],
            -1 => [
                'label' => 'All Questions',
                'show' => -1,
                'desc' => 'All questions in the database',
                'tooltip' => 'Show all questions, including unassigned.'
            ],
        ];
        echo '<ul class="nav nav-tabs question-tab-animated">';
        foreach ($tabItems as $tabShow => $tab) {
            $isActive = ($show == $tabShow) ? 'active' : '';
            $tabParams = $params;
            $tabParams['show'] = $tabShow;
            $tabUrl = Url::toRoute(array_merge([$currentRoute], $tabParams));
            echo '<li class="nav-item" style="position:relative;">';
            echo Html::a(
                '<span>' . $tab['label'] . '</span>' .
                '<div class="tab-desc" style="font-size:0.85em; color:#888; font-weight:400; line-height:1.1;">' . $tab['desc'] . '</div>',
                $tabUrl,
                [
                    'class' => 'nav-link ' . $isActive,
                    'style' => 'font-weight:' . ($isActive ? 'bold' : 'normal') . '; transition: background 0.3s, color 0.3s; min-width: 120px;',
                    'title' => $tab['tooltip'],
                    'aria-label' => $tab['label'],
                    'tabindex' => 0,
                ]
            );
            echo '</li>';
        }
        echo '</ul>';
        ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'width:50px;'],
                'contentOptions' => function ($model) {
                return [
                    'class' => 'multiline-tooltip',
                    'style' => 'color: #404080;',
                    'data-tooltip' => $model->question,
                ];
            },
            ],
            [
                'attribute' => 'status',
                'headerOptions' => ['style' => 'width:40px;'],
                // Remove the clickable box from the header
                'header' => '',
                'label' => '',
                'format' => 'raw', // to render raw HTML
                'value' => function ($model) use ($questionIds, $quiz_id) {
                $isChecked = in_array($model->id, $questionIds);
                $status = $isChecked ? 'Unlink this question from the quiz' : 'Link this question to the quiz';
                return Html::checkbox('status[]', $isChecked, [
                    'class' => 'status-checkbox',
                    'question-id' => $model->id,
                    'quiz-id' => $quiz_id,
                    'title' => $status,
                    'aria-label' => $status,
                ]);
            },
            ],
            [
                'attribute' => 'question',
                'label' => $QuestionLabelText,
                'format' => 'raw',
                'value' => function ($model) use ($quiz_id) {
                $pattern = '/<pre>(.*?)<\/pre>(.*)/s';
                if (preg_match($pattern, $model->question, $matches)) {
                    $questionText = '...' . $matches[1] . $matches[2];
                } else {
                    $questionText = $model->question;
                }
                $truncatedText = mb_substr($questionText, 0, 100) . (mb_strlen($questionText) > 100 ? '...' : '');
                $editUrl = Url::toRoute(['update', 'id' => $model->id, 'quiz_id' => $quiz_id]);
                return Html::a(Html::encode($truncatedText), $editUrl, [
                    'style' => 'color: #0a58ca; text-decoration: none; cursor: pointer;',
                    'title' => 'Click to edit this question'
                ]);
            },
            ],
            [
                'attribute' => 'label',
                'label' => 'Label',
                'headerOptions' => ['style' => 'width:200px;'],
                'contentOptions' => ['style' => 'color: #404080;'],
            ],
            [
                'attribute' => 'order',
                'label' => 'Sort',
                'headerOptions' => ['style' => 'width:60px;'],
                'contentOptions' => ['style' => 'color: #404080;'],
                'value' => function ($model) {
                return $model->order === null ? '' : $model->order;
            },
            ],
            [
                'attribute' => 'id',
                'label' => 'id',
                'headerOptions' => ['style' => 'width:30px;'],
                'contentOptions' => function ($model) {
                return [
                    'class' => 'multiline-tooltip',
                    'style' => 'color: #404080;',
                    'data-tooltip' => $model->question,
                ];
            },
            ],
            [
                'label' => 'Actions',
                'headerOptions' => ['style' => 'width:90px;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'format' => 'raw',
                'value' => function ($model) use ($quiz_id) {
                $viewUrl = Url::toRoute(['view', 'id' => $model->id, 'quiz_id' => $quiz_id, 'returnUrl' => 'index']);
                $viewIcon = Html::a('👁️', $viewUrl, [
                    'title' => 'View this question',
                    'style' => 'color: #0d6efd; text-decoration: none; font-size: 16px; margin-right: 8px;',
                ]);
                $deleteButton = Html::button('🗑️', [
                    'title' => 'Delete this question',
                    'style' => 'color: #dc3545; text-decoration: none; font-size: 16px; background: none; border: none; cursor: pointer;',
                    'class' => 'delete-question-btn',
                    'data-question-id' => $model->id,
                    'data-quiz-id' => $quiz_id,
                    'data-question-text' => mb_substr(strip_tags($model->question), 0, 50) . '...'
                ]);
                return $viewIcon . $deleteButton;
            },
            ],
        ],
    ]); ?>


</div>

<div id="button-bar" style="display:block;">
    <p>
        <hr>
        <?php
        echo Html::a('➕ New', ['create', 'quiz_id' => $quiz['id']], [
            'class' => 'btn btn-outline-success quiz-button',
            'title' => 'Create a new question for this quiz',
            'aria-label' => 'Create new question',
        ]);
        ?>
        <span style="margin-left:50px;"> </span>

        <?php
        echo Html::button('🔗 Link All', [
            'class' => 'btn btn-outline-secondary quiz-button',
            'onclick' => 'checkAllCheckboxes(true);',
            'title' => 'Link all questions to this quiz',
            'aria-label' => 'Link all questions',
        ]);
        echo Html::button('🔗 Unlink All', [
            'class' => 'btn btn-outline-secondary quiz-button',
            'onclick' => 'checkAllCheckboxes(false);',
            'title' => 'Unlink all questions from this quiz',
            'aria-label' => 'Unlink all questions',
        ]);
        ?>
        <span style="margin-left:50px;"> </span>
        <?php
        echo Html::a('📥 import', ['import', 'quiz_id' => $quiz['id']], [
            'class' => 'btn btn-outline-secondary quiz-button',
            'title' => 'Import questions in bulk for this quiz',
            'aria-label' => 'Import questions',
        ]);
        echo Html::a('📤 export', ['export', 'quiz_id' => $quiz['id']], [
            'class' => 'btn btn-outline-secondary quiz-button',
            'title' => 'Export all questions for this quiz',
            'aria-label' => 'Export questions',
        ]);
        ?>
        <span style="margin-left:50px;"> </span>
        <?php
        echo Html::a('📄 PDF', '#', [
            'class' => 'btn btn-outline-primary quiz-button pdf-download-btn',
            'title' => 'Generate PDF with all questions for this quiz',
            'aria-label' => 'Generate PDF',
            'data-quiz-id' => $quiz['id'],
            'data-quiz-name' => $quiz['name']
        ]);
        ?>
        <span style="margin-left:50px;"> </span>
        <?php
        echo Html::a('✏️ Multi Edit', ['multiple-update', 'quiz_id' => $quiz['id']], [
            'class' => 'btn btn-outline-secondary quiz-button',
            'title' => 'Edit multiple questions at once',
            'aria-label' => 'Multi Edit',
        ]);
        ?>
        <span style="margin-left:50px;"> </span>
        <!-- <?php
        if ($show == 1) {
            echo Html::a(
                '❌ Bulk Delete Questions',
                '#',
                [
                    'class' => 'btn btn-outline-danger quiz-button delete-all-btn',
                    'id' => 'delete-all-questions-btn',
                    'title' => 'Delete all linked questions',
                    'data-quiz-id' => $quiz['id'],
                    'data-quiz-name' => $quiz['name'],
                ]
            );
        }
        ?> -->
    </p>
</div>

<!-- Include the reusable custom dialog component -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>

<?php
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

// Handle quiz status dot click to toggle active/inactive
$toggleStatusScript = <<<JS
$(document).ready(function() {
    $('#quiz-status-dot').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var dot = $(this);
        var quizId = dot.data('quiz-id');
        var currentActive = parseInt(dot.data('active'));
        var newActive = currentActive === 1 ? 0 : 1;
        var statusText = newActive === 1 ? 'active' : 'inactive';
            
        // Make AJAX call to toggle status
        $.ajax({
            url: '$activateApiUrl',
            type: 'POST',
            data: {
                _csrf: '$csrfToken',
                id: quizId,
                active: newActive
            },
            success: function(response) {
                // Update the dot's appearance
                dot.data('active', newActive);
                
                if (newActive === 1) {
                    dot.removeClass('dot-red').addClass('dot-green');
                    dot.attr('title', 'Quiz is active - Click to deactivate');
                } else {
                    dot.removeClass('dot-green').addClass('dot-red');
                    dot.attr('title', 'Quiz is inactive - Click to activate');
                }
                
                // Update the global quizIsActive variable for test quiz button
                if (typeof window.quizIsActive !== 'undefined') {
                    window.quizIsActive = newActive;
                }
                
                // Hide loading indicator
                $('#modalOverlay').hide();
                $('#modalMessage').text('Please wait...');
                
                // Show success message (optional)
                console.log('Quiz status updated to: ' + statusText);
                
                // Optional: You can also reload the page to reflect changes everywhere
                // location.reload();
            },
            error: function(xhr, status, error) {
                $('#modalOverlay').hide();
                $('#modalMessage').text('Please wait...');
                alert('Error updating quiz status: ' + error);
                console.error('Error:', error);
            }
        });
    });
});
JS;

$this->registerJs($toggleStatusScript);

// Handle copy quiz button click with custom dialog
$copyUrl = Url::to(['quiz/copy', 'id' => $quiz['id']]);
$copyQuizScript = <<<JS
$(document).ready(function() {
    $('#copy-quiz-button').click(function(e) {
        e.preventDefault();
        
        var quizId = $(this).data('quiz-id');
        var quizName = $(this).data('quiz-name');
        
        window.showCustomDialog(
            '📋 Copy Quiz',
            'Are you sure you want to copy the quiz "<strong>' + quizName + '</strong>"?<br><br>This will create a duplicate with all its questions.',
            function() {
                // Show loading message
                $('#modalMessage').html('Copying quiz...<br><small>Please wait</small>');
                $('#modalOverlay').show();
                
                // Redirect to the copy action
                window.location.href = '$copyUrl';
            }
        );
    });
});
JS;

$this->registerJs($copyQuizScript);

// Handle delete all questions button click with custom dialog
$deleteAllUrl = Url::to(['question/bulk-delete', 'quiz_id' => $quiz['id']]);
$deleteAllScript = <<<JS
$(document).ready(function() {
    $('#delete-all-questions-btn').click(function(e) {
        e.preventDefault();
        
        var quizId = $(this).data('quiz-id');
        var quizName = $(this).data('quiz-name');
        
        window.showCustomDialog(
            '❌ BULK Delete All Questions',
            'Are you sure you want to delete <strong>ALL active quiz questions</strong> for the quiz "<strong>' + quizName + '</strong>"?<br><br><span style="color:#dc3545;font-size:1.1em;">⚠️ WARNING: This action cannot be undone!<br>All questions associated with this quiz will be permanently deleted.</span>',
            function() {
                // Show loading message
                $('#modalMessage').html('Deleting all questions...<br><small>Please wait</small>');
                $('#modalOverlay').show();
                
                // Redirect to the delete action
                window.location.href = '$deleteAllUrl';
            }
        );
    });
});
JS;

$this->registerJs($deleteAllScript);

// Handle delete question button click with custom dialog
$csrfToken = Yii::$app->request->getCsrfToken();
$deleteBaseUrl = Url::to(['question/delete']);
$deleteQuestionScript = <<<JS
$(document).ready(function() {
    $('.delete-question-btn').on('click', function(e) {
        e.preventDefault();
        
        var questionId = $(this).data('question-id');
        var quizId = $(this).data('quiz-id');
        var questionText = $(this).data('question-text');
        
        window.showCustomDialog(
            '❌ Delete Question',
            'Are you sure you want to delete this question?<br><br><strong>Question preview:</strong><br><em>' + questionText + '</em><br><br><span style="color:#dc3545;">⚠️ Warning: This action cannot be undone!</span>',
            function() {
                // Create a hidden form to submit the POST request
                var form = $('<form>', {
                    'method': 'POST',
                    'action': '$deleteBaseUrl' + '?id=' + questionId + '&quiz_id=' + quizId
                });
                
                // Add CSRF token
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_csrf',
                    'value': '$csrfToken'
                }));
                
                // Append to body and submit
                form.appendTo('body').submit();
            }
        );
    });
});
JS;

$this->registerJs($deleteQuestionScript);
?>