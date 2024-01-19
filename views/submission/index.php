<?php

use app\models\Submission;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;



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

    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<div class="row">
    <div class="col-md-11">
        <p style='color:#909090;font-size:16px;'><?= $this->title ?></p>
    </div>
    <div class="col-md-1 d-flex align-items-end">
        <?php if (isset($params['quiz_id'])) { ?>
            <a href="<?= Url::to(['submission/export', 'quiz_id' => $params['quiz_id']]) ?>" class="btn btn-outline-dark quiz-button">Excel</a>
        <?php } ?>
    </div>
</div>

<div>

    <div class="submission-index">

        <?php // echo $this->render('_search', ['model' => $searchModel]);
        $contentOptionsReady = function ($model, $key, $index, $column) {
            if (!$model->finished) return ['style' => ""];
            $score = $model->no_questions > 0 ? round(($model->no_correct / $model->no_questions) * 100, 0) : 0;
            if ($model->no_answered) {
                $backgroundColor = $score < 55 ? 'lightcoral' : 'lightgreen';
            } else {
                $backgroundColor = "";
            }
            return ['style' => "background-color: $backgroundColor;"];
        };
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
                    'headerOptions' => ['style' => 'width:40px;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(strtoupper(substr($model->token, -3)), ['submission/update', 'id' => $model->id]);
                        // return $model->no_answered.'/'.$model->no_questions;
                    },
                ],
                [
                    'attribute' => 'finished',
                    'enableSorting' => false,
                    'filter' => true,
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'active-field'],
                    'header' => 'Ready',
                    'headerOptions' => ['style' => 'width:40px;'],
                    'contentOptions' => $contentOptionsReady,
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
                // [
                //     'header' => 'Voortgang',
                //     'attribute' => 'no_answered',
                //     'headerOptions' => ['style' => 'width:60px;'],
                //     'contentOptions' => function ($model, $key, $index, $column) {
                //         if ( $model->no_answered == $model->no_questions ) {
                //             $backgroundColor = 'lightgreen';
                //         } else {
                //             $backgroundColor = "";
                //         }
                //         return ['style' => "background-color: $backgroundColor;"];
                //     },
                //     'value' => function ($model) {
                //         return $model->no_questions > 0 ? round(($model->no_answered / $model->no_questions) * 100, 0) . '%' : '0';
                //     },
                // ],
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
                        if (!$model->finished) return ['style' => ""];
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
                        $displayedName = mb_substr($fullName, 0, 26) . (mb_strlen($fullName) > 26 ? '...' : '');

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
                        $value = Yii::$app->formatter->asDatetime($model->start_time, 'php:d-m H:i') ;
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
                // [
                //     'label' => 'Questions',
                //     'attribute' => 'question_order',
                //     'enableSorting' => false,
                //     'filter' => false,
                //     'contentOptions' => function ($model, $key, $index, $column) {

                //         $numbersArray = explode(" ", $model->question_order);
                //         foreach ($numbersArray as $key => &$value) {
                //             $value = ($key + 1) . ':' . $value;
                //         }
                //         $result = implode(" ", $numbersArray);

                //         return ['title' => $result];
                //     },
                //     'format' => 'raw',
                //     'value' => function ($model) {
                //         return "<span style='color:#909090'>" .
                //             mb_substr($model->question_order, 0, 45) . (mb_strlen($model->question_order) > 45 ? '...' : '')
                //             . "</span>";
                //     }
                // ],

                [
                    'label' => 'Duration',
                    'enableSorting' => false,
                    'attribute' => 'end_time',
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
                        return "<span style='color:".$color."'>" . $value . "</span>";
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
                    'attribute' => 'ip_address',
                    'label' => 'ip',
                    'headerOptions' => ['style' => 'width:60px;'],
                ],

                // [
                //     'attribute' => 'answeredScore',
                //     'label' => 'Score',
                //     'value' => function ($model) {
                //         return $model->answeredScore;
                //     },
                //     'format' => 'raw', // or 'decimal', depending on how you want to format it
                // ],
                // [
                //     'label' => 'Answers',
                //     'attribute' => 'answer_order',
                //     'enableSorting' => false,
                //     'filter' => false,
                //     'headerOptions' => ['style' => 'width:200px;'],
                //     'value' => function ($model) {
                //         return mb_substr($model->answer_order, 0, 15) . (mb_strlen($model->answer_order) > 15 ? '...' : '');
                //     }
                // ],

            ],
        ]); ?>

    </div>
</div>