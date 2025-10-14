<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

// Include the function for rendering questions with allowed HTML tags
require_once Yii::getAlias('@app/views/include/functions.php');

$this->title = 'Edit Question Labels - ' . Html::encode($quiz['name']);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .header .quiz-info {
            color: #666;
            font-size: 14px;
        }

        .question-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
        }

        .question-content {
            flex: 1;
            min-width: 0;
        }

        .question-preview {
            max-height: 120px; /* Approximately 5 lines at 24px line-height */
            overflow-y: auto;
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            padding: 10px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 24px;
            word-wrap: break-word;
            margin-bottom: 10px;
        }

        .question-preview pre {
            background-color: #e8e8e8;
            border: 1px solid #ccc;
            padding: 10px;
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            overflow-x: auto;
            margin: 5px 0;
        }

        .question-preview code {
            background-color: #e8e8e8;
            padding: 2px 6px;
            font-family: monospace;
            border-radius: 3px;
        }

        .question-preview b {
            font-weight: bold;
        }

        .question-preview i {
            font-style: italic;
        }

        .question-preview::-webkit-scrollbar {
            width: 8px;
        }

        .question-preview::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .question-preview::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .question-preview::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .question-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }

        .label-input-container {
            flex: 0 0 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .label-input-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .label-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .label-input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .submit-container {
            position: sticky;
            bottom: 0;
            background-color: #fff;
            padding: 20px;
            border-top: 2px solid #ddd;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .btn-back {
            background-color: #666;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 15px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background-color: #555;
        }

        .flash-message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .flash-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .flash-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .no-questions {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 18px;
        }

        .question-counter {
            background-color: #f0f0f0;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="quiz-info">
            Quiz: <strong><?= Html::encode($quiz['name']) ?></strong> (ID: <?= $quiz['id'] ?>)
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="flash-message flash-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="flash-message flash-error">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($questions)): ?>
        <div class="no-questions">
            No active questions found for this quiz.
        </div>
        <div style="text-align: center;">
            <?= Html::a('Back to Quiz List', ['index'], ['class' => 'btn-back']) ?>
        </div>
    <?php else: ?>
        <div class="question-counter">
            Total Questions: <strong><?= count($questions) ?></strong>
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'post',
            'action' => ['edit-labels', 'id' => $quiz['id']],
        ]); ?>

            <?php foreach ($questions as $index => $question): ?>
                <div class="question-item">
                    <div class="question-content">
                        <div class="question-meta">
                            Question #<?= ($index + 1) ?> (ID: <?= $question['id'] ?>)
                        </div>
                        <div class="question-preview" title="Scroll to see full question">
                            <?= escapeHtmlExceptTags($question['question']) ?>
                        </div>
                    </div>
                    <div class="label-input-container">
                        <label class="label-input-label" for="label-<?= $question['id'] ?>">
                            Label:
                        </label>
                        <input 
                            type="text" 
                            id="label-<?= $question['id'] ?>" 
                            name="labels[<?= $question['id'] ?>]" 
                            class="label-input" 
                            value="<?= Html::encode($question['label'] ?? '') ?>"
                            maxlength="100"
                            placeholder="Enter label (max 100 chars)"
                        />
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="submit-container">
                <button type="submit" class="btn-submit">
                    💾 Save All Labels
                </button>
                <?= Html::a('Cancel', ['index'], ['class' => 'btn-back']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>

</body>
</html>

