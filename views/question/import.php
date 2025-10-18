<?php

use yii\helpers\Url;
use yii\helpers\Html;

$quiz_id = Yii::$app->request->get('quiz_id', null);

// Disable the default flash message display for this page
$this->params['hide_flash_messages'] = true;

?>

<style>
    .container {
        display: flex;
    }
    .col {
        margin-left:20px;
    }
    
    /* Flash message styling */
    .alert {
        position: relative;
        padding: 15px 20px;
        margin: 0 auto 20px auto;
        border: 1px solid transparent;
        border-radius: 6px;
        font-size: 14px;
        line-height: 1.5;
        max-width: 60rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    
    .btn-close {
        position: absolute;
        top: 8px;
        right: 12px;
        padding: 8px 12px;
        color: inherit;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.6;
        line-height: 1;
    }
    
    .btn-close:hover {
        opacity: 1;
        background-color: rgba(0,0,0,0.1);
        border-radius: 3px;
    }
    
    .alert strong {
        font-weight: 600;
        margin-right: 5px;
    }
    
    .alert pre {
        background-color: rgba(0,0,0,0.05);
        padding: 8px 12px;
        border-radius: 3px;
        margin: 8px 0 0 0;
        font-size: 13px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
</style>

<div style="margin-top:20px;">
    <!-- Flash Messages -->
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>Error:</strong> <?= nl2br(Html::encode(Yii::$app->session->getFlash('error'))) ?>
        </div>
    <?php endif; ?>
    
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>Success:</strong> <?= nl2br(Html::encode(Yii::$app->session->getFlash('success'))) ?>
        </div>
    <?php endif; ?>

<div class="card" style="width: 60rem;padding:30px;box-shadow: 0 2px 5px rgba(0,0,0,0.2);background-color:#fdfdfd;">
    <h4>Bulk Import for Quiz '<?= isset($quiz['name']) ? $quiz['name'] : "-" ?>'</h4>
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