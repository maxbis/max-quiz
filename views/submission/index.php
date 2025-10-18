<?php

use app\models\Submission;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

use yii\widgets\Pjax;

$this->title = 'Results for ' . $quizName;
// echo "<p style='color:#909090;font-size:16px;'>".$this->title.'</p>';
$params = Yii::$app->request->getQueryParams();
?>

<style>
    /* Style for the condensed table */
    .condensed-table {
        width: 100%;
        border-collapse: collapse;
        /* Ensures that the border is collapsed (no space between them) */
        border: 1px solid #b0b0b0;
        font-size: 14px;
    }

    /* Style for table cells */
    .condensed-table td,
    .condensed-table th {
        padding: 2px;
        border: 1px solid #e0e0e0;
    }

    /* Optional: Style for the table header */
    .condensed-table th {
        background-color: #f8f8f8;
        /* Light background for header */
        text-align: left;
        white-space: nowrap;
        vertical-align: bottom;
    }

    /* Optional: Remove the bottom border from the last row */
    .condensed-table tr:last-child td {
        border-bottom: none;
    }

    .delete-button {
        text-decoration: none;
        cursor: pointer;
    }

    .delete-button:hover {
        background-color: #909090;
    }

    .grid-view tr:hover {
        background-color: #e0e0e0;
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
</style>

<?php
$statusClass = $quizActive == 1 ? 'dot-green' : 'dot-red';
?>

<div class="row">
    <div class="col-md-8">
        <p style='color:#909090;font-size:16px;'>
        <h3>
            <div class="dot <?= $statusClass ?>"></div>
            <?= $this->title ?>
        </h3>
        </p>
    </div>
    <div class="col-md-4 d-flex align-items-center justify-content-end pe-0">

        <?php
        echo Html::a('🧹&nbsp;Clean', '#', [
            'title' => 'Delete Old Unfinished',
            'class' => 'btn btn-outline-dark btn-sm me-2 clean-btn',
            'style' => 'min-width: 80px; padding: 6px 12px;',
            'data-quiz-id' => $params['quiz_id']
        ]);

        if (isset($params['quiz_id'])) { 
            echo Html::a('⬇️&nbsp;Results', '#', [
                'class' => 'btn btn-outline-dark btn-sm me-2 export-results-btn',
                'style' => 'min-width: 80px; padding: 6px 12px;',
                'title' => 'Download results as CSV file',
                'data-quiz-id' => $params['quiz_id']
            ]);
            echo Html::a('⬇️&nbsp;&nbsp;Stats', '#', [
                'class' => 'btn btn-outline-dark btn-sm export-stats-btn',
                'style' => 'min-width: 80px; padding: 6px 12px;',
                'title' => 'Download statistics as CSV file',
                'data-quiz-id' => $params['quiz_id']
            ]);
        } ?>

    </div>
</div>

<!-- Modern Dialog Modal -->
<div id="customDialog" class="custom-dialog-overlay" style="display: none;">
    <div class="custom-dialog">
        <div class="custom-dialog-header">
            <h4 id="dialogTitle">Confirm Action</h4>
            <button type="button" class="custom-dialog-close" id="dialogCloseBtn">&times;</button>
        </div>
        <div class="custom-dialog-body">
            <p id="dialogMessage">Are you sure you want to proceed?</p>
            <div id="dialogInputContainer" style="display: none;">
                <label for="dialogInput">Filename (without extension):</label>
                <input type="text" id="dialogInput" class="form-control" placeholder="Enter filename...">
            </div>
        </div>
        <div class="custom-dialog-footer">
            <button type="button" class="btn btn-secondary" id="dialogCancelBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="dialogConfirmBtn">Confirm</button>
        </div>
    </div>
</div>

<style>
.custom-dialog-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

.custom-dialog {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    animation: dialogSlideIn 0.3s ease-out;
}

@keyframes dialogSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.custom-dialog-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.custom-dialog-header h4 {
    margin: 0;
    color: #495057;
    font-weight: 600;
    font-size: 1.25rem;
}

.custom-dialog-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.custom-dialog-close:hover {
    background-color: #e9ecef;
    color: #495057;
}

.custom-dialog-body {
    padding: 24px;
}

.custom-dialog-body p {
    margin: 0 0 16px 0;
    color: #495057;
    line-height: 1.5;
}

.custom-dialog-body label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #495057;
}

.custom-dialog-body input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.custom-dialog-body input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.custom-dialog-footer {
    padding: 16px 24px 24px;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background-color: #f8f9fa;
}

.custom-dialog-footer .btn {
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.custom-dialog-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.custom-dialog-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
    transform: translateY(-1px);
}

.custom-dialog-footer .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.custom-dialog-footer .btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
    transform: translateY(-1px);
}
</style>

<div>

    <div class="submission-index">

        <?php // echo $this->render('_search', ['model' => $searchModel]);
        $contentOptionsReady = function ($model, $key, $index, $column) {
            if (!$model->finished)
                return ['style' => ""];
            $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
            if ($model->no_answered) {
                $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
            } else {
                $backgroundColor = "";
            }
            return ['style' => "background-color: $backgroundColor;"];
        };
        ?>

        <?php
        if ($quizActive) {
            Pjax::begin(['id' => 'myPjaxGridView', 'timeout' => false, 'enablePushState' => true]);
            echo "Active";
        }
        ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'condensed-table'],
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'width:30px;'],
                ],
                [
                    'label' => 'Code', // You can change the label as needed
                    'attribute' => 'token',
                    'enableSorting' => true,
                    'headerOptions' => ['style' => 'width:40px;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(strtoupper(substr($model->token, 0, 3)), ['submission/update', 'id' => $model->id]);
                        // return $model->no_answered.'/'.$model->no_questions;
                    },
                ],
                [
                    'attribute' => 'finished',
                    'enableSorting' => true,
                    'filter' => true,
                    'label' => 'Ready',
                    // 'header' => 'Ready',
                    'headerOptions' => ['style' => 'width:40px;'],
                    'contentOptions' => $contentOptionsReady,
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::checkbox('finished', $model->finished, ['value' => $model->id, 'disabled' => true,]);
                    },
                ],
                [
                    'label' => 'Voortgang',
                    'attribute' => 'no_answered',
                    'headerOptions' => ['style' => 'width:60px;'],
                    'contentOptions' => function ($model, $key, $index, $column) {
                        return ['style' => "position: relative;"];
                    },
                    'format' => 'raw', // Set format to raw to allow HTML content
                    'value' => function ($model) {
                        $percentage = $model->no_questions > 0 ? round(($model->no_answered / $model->no_questions) * 100, 0) : 0;
                        return "<div style='width: $percentage%; height: 100%; background-color: lightgreen; position: absolute; top: 0; left: 0;'></div>"
                            . "<div style='position: relative;'>$percentage%</div>";
                    },
                ],
                [
                    'label' => 'Score',
                    'attribute' => 'answeredScore',
                    'headerOptions' => ['style' => 'width:80px;'],
                    'contentOptions' => function ($model, $key, $index, $column) {
                        if ($model->finished) {
                            //finished
                            $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                        } else {
                            // progress
                            $score = $model->no_correct > 0 ? round(($model->no_correct / $model->no_answered) * 100, 0) : 0;
                        }
                        // $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                        if ($model->no_answered) {
                            $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
                        } else {
                            $backgroundColor = "";
                        }
                        return ['style' => "background-color: $backgroundColor;"];
                    },
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->finished) {
                            //finished
                            return $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) . '%' : '0';
                        } else {
                            // progress
                            return $model->no_correct > 0 ? round(($model->no_correct / $model->no_answered) * 100, 0) . '%' : '0';
                        }
                    },
                ],
                [
                    'label' => 'Student',
                    'attribute' => 'first_name',
                    'headerOptions' => ['style' => 'width:180px;'],
                    'contentOptions' => function ($model, $key, $index, $column) {
                        if (!$model->finished)
                            return ['style' => ""];
                        $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
                        if ($model->no_answered) {
                            $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
                        } else {
                            $backgroundColor = "";
                        }
                        return ['style' => "background-color: $backgroundColor;"];
                    },
                    // 'value' => function ($model) {
                    //     $fullName = $model->first_name.' '.$model->last_name;
                    //     return mb_substr($fullName, 0, 26) . (mb_strlen($fullName) > 26 ? '...' : '');
                    // },
                    'format' => 'raw',
                    'value' => function ($model) {
                        $fullName = $model->first_name . ' ' . $model->last_name;
                        $displayedName = mb_substr($fullName, 0, 20) . (mb_strlen($fullName) > 20 ? '...' : '');

                        // Create the URL
                        $url = Url::to(['/site/results', 'token' => $model->token]);

                        // Return the hyperlink
                        return Html::a($displayedName, $url);
                    },
                ],
                [
                    'attribute' => 'class',
                    'label' => 'klas',
                    'headerOptions' => ['style' => 'width:60px;'],
                    'contentOptions' => $contentOptionsReady,

                ],
                [
                    'attribute' => 'no_answered',
                    'label' => 'Progr.',
                    'headerOptions' => ['style' => 'width:60px;', 'title' => 'Number of Questions / Number of Answers'],
                    'contentOptions' => $contentOptionsReady,
                    'format' => 'raw',
                    'value' => function ($model) {
                        // return Html::a($model->no_answered.'/'.$model->no_questions, ['submission/update', 'id' => $model->id]);
                        return $model->no_answered . '/' . $model->no_questions;
                    },
                ],
                [
                    'attribute' => 'no_correct',
                    'label' => 'Corr',
                    'headerOptions' => ['style' => 'width:60px;', 'title' => 'Number of Correct Answers'],
                    'contentOptions' => $contentOptionsReady,

                ],
                [
                    'label' => 'Start time',
                    'attribute' => 'start_time',
                    'enableSorting' => true,
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:100px;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        $value = Yii::$app->formatter->asDatetime($model->start_time, 'php:d-m H:i');
                        return "<span style='color:#909090'>" . $value . "</span>";
                    }
                ],
                [
                    'attribute' => 'last_updated',
                    'label' => 'Last Update',
                    'headerOptions' => ['style' => 'width:100px;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        $formattedDate = Yii::$app->formatter->asDatetime($model->last_updated, 'php:d-m H:i');
                        return "<span style='color:#909090'>$formattedDate</span>";
                    },
                ],
                [
                    'label' => 'Duration',
                    'enableSorting' => false,
                    'attribute' => 'duration',
                    'filter' => false,
                    'headerOptions' => ['style' => 'width:60px;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (isset($model->end_time)) {
                            $diffInSeconds = strtotime($model->end_time) - strtotime($model->start_time);
                            $color = "#004000";
                        } else {
                            $diffInSeconds = strtotime($model->last_updated) - strtotime($model->start_time);
                            $color = "#909090";
                        }
                        $minutes = floor($diffInSeconds / 60);
                        $seconds = $diffInSeconds % 60;
                        $value = (str_pad($minutes, 2, '0', STR_PAD_LEFT) . ":" . str_pad($seconds, 2, '0', STR_PAD_LEFT));
                        return "<span style='color:" . $color . "'>" . $value . "</span>";
                    }
                ],
                [
                    'label' => 'Now @ Q',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $numbers = explode(' ', $model->question_order);
                        $index = $model->no_answered;

                        if (isset($numbers[$index])) {
                            return Html::a($numbers[$index], ['/question/view', 'id' => $numbers[$index]]);
                        }

                        return '-';
                    },
                ],
                [
                    'attribute' => 'user_agent',
                    'label' => 'User Agent',
                    'headerOptions' => ['style' => 'width:90px;'],
                    'value' => function ($model) {
                        // Split the user_agent by spaces and return the first word
                        return $model->user_agent ? strtok($model->user_agent, ' ') : '';
                    },
                    'contentOptions' => function ($model) {
                        // Set the title attribute for the tooltip with the full user_agent
                        return ['title' => $model->user_agent ?: ''];
                    },
                ],
                [
                    'attribute' => 'ip_address',
                    'label' => 'IP',
                    'headerOptions' => ['style' => 'width:60px;'],
                ],
                [
                    'class' => ActionColumn::class,
                    'headerOptions' => ['style' => 'width:20px;'],
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            return Html::a('&#10060;', false, [
                                'class' => 'ajax-delete delete-button',
                                'title' => 'Delete',
                                'data' => [
                                    'url' => Url::to(['submission/delete', 'id' => $model->id]),
                                    'name' => $model->first_name . ' ' . $model->last_name,
                                ],
                            ]);
                        },
                    ],
                ],

            ],

        ]);

        if ($quizActive) {
            Pjax::end();
        }

        // Generate URLs and CSRF token for JavaScript
        $cleanUrl = Url::to(['submission/delete-unfinished']);
        $exportUrl = Url::to(['submission/export']);
        $exportStatsUrl = Url::to(['submission/export-stats']);
        $csrfToken = Yii::$app->request->csrfToken;

        $script = <<<JS

            // Modern Dialog Functions
            window.showCustomDialog = function(title, message, onConfirm, showInput, defaultValue) {
                showInput = showInput || false;
                defaultValue = defaultValue || '';
                
                $('#dialogTitle').text(title);
                $('#dialogMessage').text(message);
                
                if (showInput) {
                    $('#dialogInputContainer').show();
                    $('#dialogInput').val(defaultValue);
                    setTimeout(function() {
                        $('#dialogInput').focus();
                    }, 100);
                } else {
                    $('#dialogInputContainer').hide();
                }
                
                $('#customDialog').show();
                
                // Store the confirm callback
                window.currentDialogCallback = onConfirm;
            };

            window.closeCustomDialog = function() {
                $('#customDialog').hide();
                window.currentDialogCallback = null;
            };

            // Handle dialog close button (X)
            $('#dialogCloseBtn').on('click', function() {
                window.closeCustomDialog();
            });

            // Handle dialog cancel button
            $('#dialogCancelBtn').on('click', function() {
                window.closeCustomDialog();
            });

            // Handle dialog confirm button
            $('#dialogConfirmBtn').on('click', function() {
                if (window.currentDialogCallback) {
                    window.currentDialogCallback();
                }
                window.closeCustomDialog();
            });

            // Handle dialog input enter key
            $('#dialogInput').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    $('#dialogConfirmBtn').click();
                }
            });

            // Handle dialog overlay click to close
            $('#customDialog').on('click', function(e) {
                if (e.target === this) {
                    window.closeCustomDialog();
                }
            });

            // Handle escape key to close dialog
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#customDialog').is(':visible')) {
                    window.closeCustomDialog();
                }
            });

            $(document).on('click', '.ajax-delete', function (e) {
                e.preventDefault();
                var url = $(this).data('url');
                var name = $(this).data('name');
                window.showCustomDialog(
                    'Delete Submission',
                    'Are you sure to delete the submission for ' + name + '?',
                    function() {
                        $.post(url, function (data) {
                            console.log('AJAX delete succes');
                            location.reload(); // Reload the page or use Pjax to refresh the GridView
                        }).fail(function () {
                            console.log('AJAX delete error');
                            alert('Error occurred while deleting.');
                        });
                    }
                );
            });

            // Clean button
            $('.clean-btn').on('click', function(e) {
                e.preventDefault();
                window.showCustomDialog(
                    'Clean Unfinished Submissions',
                    'All unfinished submissions that are inactive for more than 2 hours will be deleted. Do you want to proceed?',
                    function() {
                        var quizId = $('.clean-btn').data('quiz-id');
                        // Use POST request as required by the controller
                        $.post('$cleanUrl?quiz_id=' + quizId, {
                            _csrf: '$csrfToken'
                        }, function(data) {
                            // Handle successful response
                            if (data.success) {
                                // Just reload to show the flash message banner
                                location.reload();
                            } else {
                                alert('Error: ' + (data.message || 'Unknown error occurred'));
                            }
                        }).fail(function(xhr, status, error) {
                            console.error('Clean error:', xhr.responseText);
                            alert('Error occurred while cleaning submissions: ' + error);
                        });
                    }
                );
            });

            var refreshIntervalId = setInterval(function () {
                $.pjax.reload({ container: '#myPjaxGridView' });
            }, 30000); // 20000 milliseconds = 20 seconds

            setTimeout(function() {
                clearInterval(refreshIntervalId);
            }, 2700000); // 2700000 milliseconds = 45 minutes

            // Export buttons with filename prompt
            $('.export-results-btn').on('click', function(e) {
                e.preventDefault();
                var quizId = $(this).data('quiz-id');
                var defaultName = 'quiz-results-' + quizId + '-' + new Date().toISOString().slice(0,10);
                window.showCustomDialog(
                    'Export Results',
                    'Enter filename for export (without extension):',
                    function() {
                        var filename = $('#dialogInput').val().trim();
                        if (filename !== '') {
                            var url = '$exportUrl?quiz_id=' + quizId + '&filename=' + encodeURIComponent(filename);
                            window.location.href = url;
                        }
                    },
                    true,
                    defaultName
                );
            });

            $('.export-stats-btn').on('click', function(e) {
                e.preventDefault();
                var quizId = $(this).data('quiz-id');
                var defaultName = 'quiz-stats-' + quizId + '-' + new Date().toISOString().slice(0,10);
                window.showCustomDialog(
                    'Export Stats',
                    'Enter filename for export (without extension):',
                    function() {
                        var filename = $('#dialogInput').val().trim();
                        if (filename !== '') {
                            var url = '$exportStatsUrl?quiz_id=' + quizId + '&filename=' + encodeURIComponent(filename);
                            window.location.href = url;
                        }
                    },
                    true,
                    defaultName
                );
            });

        JS;

        $this->registerJs($script);

        ?>

    </div>
</div>