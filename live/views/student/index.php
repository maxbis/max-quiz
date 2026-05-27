<?php

use yii\helpers\Html;
use yii\helpers\Url;

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Live Quiz</title>
    <style>
        body { margin:0; font-family:Consolas, Menlo, monospace; background:linear-gradient(160deg, #fef3c7 0%, #dbeafe 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { width:min(520px, 100%); background:white; border-radius:24px; padding:32px; box-shadow:0 24px 60px rgba(15, 23, 42, 0.18); }
        h1 { margin-top:0; font-size:2rem; }
        label { display:block; margin:14px 0 6px; font-weight:700; }
        input { width:100%; box-sizing:border-box; padding:14px 16px; border-radius:14px; border:1px solid #cbd5e1; font:inherit; }
        button { margin-top:18px; width:100%; border:0; border-radius:14px; padding:15px 16px; font:inherit; font-weight:700; color:white; background:#0f172a; cursor:pointer; }
        .helper { color:#475569; margin-bottom:10px; }
        .flash { background:#fee2e2; color:#991b1b; padding:12px 14px; border-radius:12px; margin-bottom:14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Join Live Quiz</h1>
        <p class="helper">Enter the code from the teacher screen and wait in the lobby until the first question opens.</p>
        <?php foreach (Yii::$app->session->getAllFlashes() as $flash): ?>
            <div class="flash"><?= Html::encode($flash) ?></div>
        <?php endforeach; ?>
        <form method="post" action="<?= Url::to(['join']) ?>">
            <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">
            <label for="join_code">Join code</label>
            <input id="join_code" name="join_code" required maxlength="16" value="<?= Html::encode($code) ?>" style="text-transform:uppercase;">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" required maxlength="40">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" required maxlength="40">
            <label for="class">Class</label>
            <input id="class" name="class" required maxlength="8">
            <button type="submit">Enter Lobby</button>
        </form>
    </div>
</body>
</html>
