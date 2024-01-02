<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<style>
    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<div class="card" style="width: 60rem;padding:30px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
<h4>Bulk Import</h4>
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
    </pre>
</p><p>
    Paste questions:
</p>
<div class="bulk-import-form">
    <form action="<?= Url::to(['question/bulk-import']) ?>" method="post">
        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
        <textarea name="bulkInput" rows="25" style="width:100%;overflow-y: auto; max-height: 500px;"><?=$input?></textarea>
        <br>
        <?= Html::a( 'Cancel', Yii::$app->request->referrer , ['class'=>'btn btn-outline-primary quiz-button']); ?>
        <button type="submit" class="btn btn-outline-success quiz-button">Import</button>
    </form>
</div></div>