# Custom Dialog Component - Usage Guide

This document explains how to use the reusable custom dialog component across different pages in the Max Quiz application.

## Overview

The custom dialog component provides a modern, reusable modal dialog that can be used for:
- Confirmation prompts
- User input dialogs
- Alert messages
- Any situation requiring user interaction

## Files Structure

```
assets/
  └── CustomDialogAsset.php       # Asset bundle that loads CSS and JS
views/
  └── include/
      └── _custom-dialog.php      # Dialog HTML structure
web/
  ├── css/
  │   └── custom-dialog.css       # Dialog styles
  └── js/
      └── custom-dialog.js        # Dialog JavaScript functionality
```

## How to Use on Any Page

### Step 1: Register the Asset Bundle

At the top of your view file (e.g., `views/quiz/index.php`), register the asset bundle:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

// Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);

$this->title = 'My Page Title';
?>
```

### Step 2: Include the Dialog HTML

Somewhere in your view file (typically near the top or bottom), include the dialog partial:

```php
<!-- Include the reusable custom dialog component -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>
```

### Step 3: Use the Dialog in Your JavaScript

You can now use the dialog anywhere in your page's JavaScript:

```javascript
// Simple confirmation dialog
$('.my-button').on('click', function(e) {
    e.preventDefault();
    
    window.showCustomDialog(
        'Confirm Action',                    // Title
        'Are you sure you want to proceed?', // Message
        function() {                         // onConfirm callback
            // This code runs when user clicks "Confirm"
            alert('User confirmed!');
        }
    );
});

// Dialog with input field
$('.export-button').on('click', function(e) {
    e.preventDefault();
    
    var defaultFilename = 'export-' + new Date().toISOString().slice(0,10);
    
    window.showCustomDialog(
        'Export Data',                              // Title
        'Enter filename (without extension):',      // Message
        function() {                                // onConfirm callback
            var filename = $('#dialogInput').val().trim();
            if (filename !== '') {
                window.location.href = '/export?filename=' + encodeURIComponent(filename);
            }
        },
        true,                                       // showInput = true
        defaultFilename                             // defaultValue for input
    );
});
```

## JavaScript API Reference

### `window.showCustomDialog(title, message, onConfirm, showInput, defaultValue)`

Shows the custom dialog.

**Parameters:**
- `title` (string, required) - The dialog title
- `message` (string, required) - The dialog message/prompt
- `onConfirm` (function, required) - Callback function executed when user clicks "Confirm"
- `showInput` (boolean, optional) - If `true`, shows an input field. Default: `false`
- `defaultValue` (string, optional) - Default value for the input field. Default: `''`

**Example:**
```javascript
window.showCustomDialog(
    'Delete Item',
    'Are you sure you want to delete this item?',
    function() {
        // Delete logic here
        $.post('/delete', { id: 123 }, function(data) {
            location.reload();
        });
    }
);
```

### `window.closeCustomDialog()`

Programmatically closes the dialog.

**Example:**
```javascript
window.closeCustomDialog();
```

## Keyboard Shortcuts

The dialog supports these keyboard shortcuts:
- **Enter** - Confirms the action (when input field has focus)
- **Escape** - Cancels and closes the dialog
- **Click outside** - Closes the dialog

## Complete Example

Here's a complete example for a delete button on the quiz index page:

### views/quiz/index.php
```php
<?php

use yii\helpers\Html;
use yii\helpers\Url;

// Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);

$this->title = 'Quiz List';
?>

<!-- Include the reusable custom dialog component -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>

<div class="quiz-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= Html::a('Delete Quiz', '#', [
        'class' => 'btn btn-danger delete-quiz-btn',
        'data-quiz-id' => 47,
        'data-quiz-name' => 'Math Quiz'
    ]) ?>
</div>

<?php
$deleteUrl = Url::to(['quiz/delete']);
$csrfToken = Yii::$app->request->csrfToken;

$script = <<<JS
    // Delete quiz with confirmation
    $('.delete-quiz-btn').on('click', function(e) {
        e.preventDefault();
        
        var quizId = $(this).data('quiz-id');
        var quizName = $(this).data('quiz-name');
        
        window.showCustomDialog(
            'Delete Quiz',
            'Are you sure you want to delete "' + quizName + '"?',
            function() {
                $.post('$deleteUrl', {
                    id: quizId,
                    _csrf: '$csrfToken'
                }, function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        );
    });
JS;

$this->registerJs($script);
?>
```

## Using in Controllers

You can also trigger downloads from controller actions after showing a dialog. Here's an example:

### views/quiz/index.php
```php
<?php
app\assets\CustomDialogAsset::register($this);
?>

<!-- Include the dialog -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>

<?= Html::a('⬇️ Export Results', '#', [
    'class' => 'btn btn-primary export-btn',
    'data-quiz-id' => 47
]) ?>

<?php
$exportUrl = Url::to(['quiz/export']);

$script = <<<JS
    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        var quizId = $(this).data('quiz-id');
        var defaultName = 'quiz-results-' + quizId + '-' + new Date().toISOString().slice(0,10);
        
        window.showCustomDialog(
            'Export Results',
            'Enter filename for export (without extension):',
            function() {
                var filename = $('#dialogInput').val().trim();
                if (filename !== '') {
                    var url = '$exportUrl?quiz_id=' + quizId + '&filename=' + encodeURIComponent(filename);
                    window.location.href = url;
                }
            },
            true,
            defaultName
        );
    });
JS;

$this->registerJs($script);
?>
```

## Styling Customization

If you need to customize the dialog appearance for specific pages, you can add additional CSS:

```php
<?php
$this->registerCss("
    #customDialog .custom-dialog {
        max-width: 700px; /* Make dialog wider */
    }
    
    #customDialog .custom-dialog-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Purple gradient */
    }
    
    #customDialog .custom-dialog-header h4 {
        color: white;
    }
");
?>
```

## Dependencies

The custom dialog component requires:
- jQuery (already included via Yii2)
- Bootstrap 5 (for button styles, already included via Yii2)

## Browser Support

The dialog works in all modern browsers:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)

## Troubleshooting

### Dialog doesn't appear
- Make sure you registered the asset: `app\assets\CustomDialogAsset::register($this);`
- Make sure you included the HTML: `<?= $this->render('@app/views/include/_custom-dialog.php') ?>`
- Check browser console for JavaScript errors

### Dialog appears but functionality doesn't work
- Ensure jQuery is loaded before the custom-dialog.js file
- Check that there are no JavaScript errors in the console

### Styling looks wrong
- Make sure Bootstrap 5 is loaded
- Check that custom-dialog.css is being loaded (view page source)
- Clear browser cache if styles were recently updated

## Migration from Embedded Dialog

If you have pages with embedded dialog code (like the old `submission/index.php`), follow these steps to migrate:

1. Add the asset registration at the top of the file
2. Replace the embedded HTML with `<?= $this->render('@app/views/include/_custom-dialog.php') ?>`
3. Remove the embedded CSS `<style>` block for the dialog
4. Remove the JavaScript functions: `window.showCustomDialog()`, `window.closeCustomDialog()`, and all dialog event handlers
5. Keep your page-specific button click handlers that call `window.showCustomDialog()`

## Support

For questions or issues, contact the Max Quiz development team.

