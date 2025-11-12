<?php
/**
 * CUSTOM DIALOG EXAMPLE
 * 
 * This is a quick reference example showing how to use the custom dialog
 * component on any page in your application.
 * 
 * Copy and adapt this code to your own pages.
 */

use yii\helpers\Html;
use yii\helpers\Url;

// STEP 1: Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);

$this->title = 'Example Page';
?>

<!-- STEP 2: Include the dialog HTML partial -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>

<div class="example-page">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Example buttons that will trigger dialogs -->
    <div class="button-group">
        
        <!-- Simple confirmation dialog -->
        <?= Html::a('🗑️ Delete Item', '#', [
            'class' => 'btn btn-danger delete-btn',
            'data-id' => 123,
            'data-name' => 'Example Item'
        ]) ?>

        <!-- Dialog with input field -->
        <?= Html::a('⬇️ Export Data', '#', [
            'class' => 'btn btn-primary export-btn',
            'data-id' => 456
        ]) ?>

        <!-- Warning dialog -->
        <?= Html::a('⚠️ Reset Data', '#', [
            'class' => 'btn btn-warning reset-btn'
        ]) ?>

    </div>
</div>

<?php
// STEP 3: Write your JavaScript code using the dialog

// Generate URLs for AJAX calls
$deleteUrl = Url::to(['example/delete']);
$exportUrl = Url::to(['example/export']);
$resetUrl = Url::to(['example/reset']);
$csrfToken = Yii::$app->request->csrfToken;

$script = <<<JS

// Example 1: Simple confirmation dialog
$('.delete-btn').on('click', function(e) {
    e.preventDefault();
    
    var itemId = $(this).data('id');
    var itemName = $(this).data('name');
    
    window.showCustomDialog(
        'Delete Item',
        'Are you sure you want to delete "' + itemName + '"? This action cannot be undone.',
        function() {
            // This code runs when user clicks "Confirm"
            $.post('$deleteUrl', {
                id: itemId,
                _csrf: '$csrfToken'
            }, function(data) {
                if (data.success) {
                    alert('Item deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            }).fail(function() {
                alert('An error occurred while deleting.');
            });
        }
    );
});

// Example 2: Dialog with input field (for filename)
$('.export-btn').on('click', function(e) {
    e.preventDefault();
    
    var itemId = $(this).data('id');
    var defaultFilename = 'export-' + itemId + '-' + new Date().toISOString().slice(0,10);
    
    window.showCustomDialog(
        'Export Data',
        'Enter a filename for the export (without extension):',
        function() {
            // This code runs when user clicks "Confirm"
            var filename = $('#dialogInput').val().trim();
            
            if (filename !== '') {
                // Trigger file download
                var url = '$exportUrl?id=' + itemId + '&filename=' + encodeURIComponent(filename);
                window.location.href = url;
            } else {
                alert('Please enter a filename');
            }
        },
        true,              // showInput = true (shows input field)
        defaultFilename    // defaultValue for input field
    );
});

// Example 3: Warning dialog with custom message
$('.reset-btn').on('click', function(e) {
    e.preventDefault();
    
    window.showCustomDialog(
        '⚠️ Warning: Reset Data',
        'This will reset all your data to default values. Are you absolutely sure?',
        function() {
            // This code runs when user clicks "Confirm"
            $.post('$resetUrl', {
                _csrf: '$csrfToken'
            }, function(data) {
                if (data.success) {
                    alert('Data has been reset successfully!');
                    location.reload();
                }
            });
        }
    );
});

// Example 4: Programmatically close dialog
// (useful if you want to close it from code)
// window.closeCustomDialog();

JS;

$this->registerJs($script);
?>

