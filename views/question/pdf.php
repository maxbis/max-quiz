<!DOCTYPE html>
<html>

<?php
require_once Yii::getAlias('@app/views/include/functions.php');
?>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header .info {
            color: #95a5a6;
            font-size: 11px;
            margin: 5px 0 0 0;
        }
        
        .question-container {
            page-break-inside: avoid;
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ecf0f1;
            border-radius: 5px;
            background-color: #fafbfc;
        }
        
        .question-header {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            margin: -15px -15px 15px -15px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            font-size: 13px;
        }
        
        .question-text {
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 0px;
            white-space: pre-wrap;
        }
        
        .question-text pre {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 3px;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            overflow-x: auto;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            .question-container {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= htmlspecialchars($quiz['name']) ?></h1>
        <p class="info">Generated on: <?= date('F j, Y \a\t g:i A') ?></p>
    </div>

    <?php 
    $questionCount = count($questions);
    foreach ($questions as $index => $question): 
    ?>
    <div class="question-container">
        <div class="question-header">
            Question <?= $question['id'] ?> (<?= $index + 1 ?> of <?= $questionCount ?>)
        </div>
        
        <div class="question-text">
            <?= escapeHtmlExceptTags($question['question']) ?>
        </div>
        
    </div>
    <?php endforeach; ?>
</body>
</html>
