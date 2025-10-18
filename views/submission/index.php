<?php

use app\models\Submission;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

use yii\widgets\Pjax;

// Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);

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

<!-- Include the reusable custom dialog component -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>

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

            // Page-specific button handlers for the submission index page
            // Note: The dialog functions (showCustomDialog, closeCustomDialog) are now
            // loaded from custom-dialog.js via the CustomDialogAsset bundle

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