<?php

use yii\helpers\Html;

$this->title = 'Output View';
?>

<style>
    .paper-box {
        background-color: #f8f8f8;
        border: 1px solid #ddd;
        padding: 15px;
        min-height: 9em;
        max-height: 45em;
        font-family: monospace;
        margin: 20px;
        overflow: auto;
    }

    /* CSS for the <pre> tag */
    .paper-box pre {
        white-space: pre-wrap;
        /* Wrap text within <pre> tag */
        margin: 0;
    }

    .quiz-button {
        font-size: 10px;
        padding: 2px 5px;
        min-width: 55px;
        margin: 5px;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get references to the <pre> element and the copy button
    var outputText = document.getElementById("output-text");
    var copyButton = document.getElementById("copy-button");

    // Add a click event listener to the copy button
    copyButton.addEventListener("click", function() {
        // Select the text within the <pre> element
        var range = document.createRange();
        range.selectNode(outputText);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);

        // Copy the selected text to the clipboard
        try {
            document.execCommand("copy");
            alert("Text copied to clipboard!");
        } catch (error) {
            console.error("Copy to clipboard failed: " + error);
            alert("Copy to clipboard failed. Please copy the text manually.");
        }

        // Clear the selection
        window.getSelection().removeAllRanges();
    });
});
</script>


<div class="paper-box">
    <pre id="output-text"><?= Html::encode($output) ?></pre> 
</div>

<?= Html::a( 'Cancel', Yii::$app->request->referrer , ['class'=>'btn btn-outline-primary quiz-button']); ?>
<button id="copy-button" class='btn btn-outline-success quiz-button'>Copy all</button>
<?= Html::a('import', ['import'], ['class' => 'btn btn-outline-secondary quiz-button', 'title' => '']);
?>