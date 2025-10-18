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

    function removeIDLines() {
        // Get the element by its ID
        var outputTextElement = document.getElementById("output-text");
        var text = outputTextElement.textContent;

        // Split the text into an array of lines
        var lines = text.split('\n');

        var filteredLines = [];

        for (var i = 0; i < lines.length; i++) {
            // If the line starts with "ID", skip this line and the next one
            if (lines[i].startsWith("ID")) {
                i++; // Skip the next line as well
            } else {
                // Otherwise, add the line to the filtered lines
                filteredLines.push(lines[i]);
            }
        }

        // Join the filtered lines back into a single string
        var filteredText = filteredLines.join('\n');

        // Set the modified text back to the element
        outputTextElement.textContent = filteredText;
    }
</script>


<div class="paper-box">
    <pre id="output-text"><?= Html::encode($output) ?></pre>
</div>

<?= Html::a('Cancel', Yii::$app->request->referrer, ['class' => 'btn btn-outline-primary quiz-button']); ?>
<button id="copy-button" class='btn btn-outline-success quiz-button'>Copy all</button>
<?= Html::a('import', ['import', 'quiz_id' => Yii::$app->request->get('quiz_id')], ['class' => 'btn btn-outline-secondary quiz-button', 'title' => '']);?>
<button id="remove-id-button" class='btn btn-outline-danger quiz-button' onclick="removeIDLines()">Remove IDs</button>
