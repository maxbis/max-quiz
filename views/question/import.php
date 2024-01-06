<?php

use yii\helpers\Url;
use yii\helpers\Html;

$quiz_id = Yii::$app->request->get('quiz_id', null);
?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }

    .container {
        display: flex;
    }
    .col {
        margin-left:20px;
    }
</style>

<div style="margin-top:20px;">
<div class="card" style="width: 60rem;padding:30px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
    <h4>Bulk Import</h4>
    <p>
        Paste questions:
    </p>
    <div class="bulk-import-form">
        <form action="<?= Url::to(['question/bulk-import']) ?>" method="post">
            <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
            <textarea name="bulkInput" rows="25" style="width:100%;overflow-y: auto; max-height: 500px;"><?= $input ?></textarea>
            <p></p>
            <div class="container">
                <div>
                    Override Label:
                    <input type="text" name="label" value="">
                </div>
                <div>
                    <div class="col">
                        <input type="radio" id="update" name="action" value="update" checked>
                        <label for="update">For update, you need ID's to be present in the import</label>
                    </div>
                    <div class="col">
                        <input type="radio" id="insert" name="action" value="insert">
                        <label for="insert">Insert questions and if ID is given, ignore it</label>
                    </div>
                </div>
            </div>
            <p></p>
            <hr>
            <?= Html::a('Cancel', Yii::$app->request->referrer, ['class' => 'btn btn-outline-primary quiz-button']); ?>
            <button type="submit" class="btn btn-outline-success quiz-button">Import</button>
        </form>
    </div>
</div>

<div style="margin-top:100px;color:#a0a0a0">
<p> Use this syntax for bulk-import.
    <pre>
QQ
Question...
AA
Answer Option 
AC
Correct Answer Option
AA
Answer Option 
AA
Answer Option
LL
label
QQ
Next Question....
....
</pre>
</p>
</div>
</div>