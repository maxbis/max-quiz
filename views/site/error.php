<?php
/* @var $this yii\web\View */
/* @var $message string */

use yii\helpers\Html;

$this->title = 'Error';

$this->registerCss("
    .paper {
        bbox-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        background-color: #ffffff;
        padding-top: 60px;
        margin: 20px 0;
        border-radius: 5px;
        width:600px;
    }
    .error-message {
        padding:20px;
        color: #700000;
        font-size:18px;
        font-weight: bold;
        text-shadow: 1px 1px 1px #f0f0f0;
    }
    .red-box {
        border: solid 3px #d00000;
        box-shadow: 0px 0px 40px 4px rgba(255, 0, 0, 0.2);
    }
    .site-error {
        display: flex;
        justify-content: center;
        align-items: center;
    }
");
?>
<div class="site-error">
    <div class="paper">
        <div class="alert alert-danger red-box">
            <h1>❌ Error</h1>
            <div class="error-message">
                <?= nl2br(Html::encode($message)) ?>
            </div>
        </div>
    </div>
</div>