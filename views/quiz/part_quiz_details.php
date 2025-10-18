<?php
use yii\helpers\Html;

?>

<style>
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

<div class="quiz-card"
    style="max-width:750px;border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                $statusClass = $quiz['active'] == 1 ? 'dot-green' : 'dot-red';
                $statusHelp = $quiz['active'] == 1 ? 'active' : 'inactive';
                ?>
                <h3>
                    <div title="<?= $statusHelp ?>" class="dot <?= $statusClass ?>"></div>
                    <?= $quiz['name'] ?>
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">

                <p style="color:#404080;">
                    Password:
                    <?= Html::encode($quiz['password']) ?>
                    <br>
                    questions: <span id="countDisplay">
                        <?= count($questionIds); ?>
                    </span>
                </p>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <?= Html::a('✏️ Edit', ['quiz/update', 'id' => $quiz['id']], ['class' => 'btn btn-outline-primary quiz-button'], ) ?>
                <?php
                $url = Yii::$app->urlManager->createUrl(['/question/list', 'quiz_id' => $quiz['id']]);
                echo Html::a('👁️ View', $url, ['title' => 'View Questions', 'class' => 'btn btn-outline-success quiz-button',]);
                ?>
                <?= Html::a(
                    '📋 Copy',
                    ['quiz/copy', 'id' => $quiz['id']],
                    [
                        'class' => 'btn btn-outline-danger quiz-button',
                        'onclick' => 'return confirm("Are you sure you want to copy this quiz?");',
                    ],
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