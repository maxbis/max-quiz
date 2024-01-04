<?php
/* @var $this yii\web\View */
/* @var $message string */

use yii\helpers\Html;

$this->title = 'Error';
$this->registerCss("
    .paper {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        background-color: white;
        padding: 20px;
        margin: 20px 0;
        border-radius: 5px;
    }
    .error-message {
        color: salmon;
        font-weight: bold;
    }
");
?>
<div class="site-error">
    <div class="paper">
        <div class="alert alert-danger">
            <h1><?= Html::encode($this->title) ?></h1>
            <div class="error-message">
                <?= nl2br(Html::encode($message)) ?>
            </div>
        </div>
    </div>
</div>
