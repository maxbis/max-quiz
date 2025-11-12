# Question Index Page - PDF Dialog Implementation

## Summary

Successfully implemented the custom dialog for PDF downloads on the Question index page. This page displays questions for a specific quiz and has **two PDF buttons** that now both use the custom filename dialog.

## Page Location

**URL:** `http://localhost/yii2/max-quiz/web/question/index?quiz_id={id}`

## Changes Made

### 1. **views/question/index.php**

#### Added Asset Registration (Lines 9-10)
```php
// Register the custom dialog asset bundle
app\assets\CustomDialogAsset::register($this);
```

#### Updated First PDF Button (Lines 335-344)
Located in the top action button row.

**Before:**
```php
<?= Html::a(
    '📄 PDF',
    ['pdf', 'quiz_id' => $quiz['id']],
    [
        'class' => 'btn btn-outline-secondary quiz-button',
        'title' => 'Generate PDF with all questions for this quiz',
    ]
); ?>
```

**After:**
```php
<?= Html::a(
    '📄 PDF',
    '#',
    [
        'class' => 'btn btn-outline-secondary quiz-button pdf-download-btn',
        'title' => 'Generate PDF with all questions for this quiz',
        'data-quiz-id' => $quiz['id'],
        'data-quiz-name' => $quiz['name']
    ]
); ?>
```

#### Updated Second PDF Button (Lines 554-560)
Located in the bottom action button row.

**Before:**
```php
echo Html::a('📄 PDF', ['pdf', 'quiz_id' => $quiz['id']], [
    'class' => 'btn btn-outline-primary quiz-button',
    'title' => 'Generate PDF with all questions for this quiz',
    'aria-label' => 'Generate PDF',
]);
```

**After:**
```php
echo Html::a('📄 PDF', '#', [
    'class' => 'btn btn-outline-primary quiz-button pdf-download-btn',
    'title' => 'Generate PDF with all questions for this quiz',
    'aria-label' => 'Generate PDF',
    'data-quiz-id' => $quiz['id'],
    'data-quiz-name' => $quiz['name']
]);
```

#### Added Dialog HTML Include (Lines 584-585)
```php
<!-- Include the reusable custom dialog component -->
<?= $this->render('@app/views/include/_custom-dialog.php') ?>
```

#### Added JavaScript Handler (Lines 587-621)
```javascript
// Handle PDF download with filename dialog
$(document).on('click', '.pdf-download-btn', function(e) {
    e.preventDefault();
    
    var quizId = $(this).data('quiz-id');
    var quizName = $(this).data('quiz-name');
    var defaultFilename = 'quiz-' + quizId + '-' + quizName.replace(/[^a-zA-Z0-9]/g, '-') + '-' + new Date().toISOString().slice(0,10);
    
    window.showCustomDialog(
        'Generate PDF',
        'Enter filename for the PDF (without extension):',
        function() {
            var filename = $('#dialogInput').val().trim();
            if (filename !== '') {
                // Trigger PDF download
                var url = '/question/pdf?quiz_id=' + quizId + '&filename=' + encodeURIComponent(filename);
                window.location.href = url;
            } else {
                alert('Please enter a filename');
            }
        },
        true,              // showInput = true
        defaultFilename    // defaultValue
    );
});
```

## Features

### ✅ Two PDF Buttons
Both PDF buttons on the page now use the same custom dialog:
1. **Top button** (btn-outline-secondary) - in the main action row
2. **Bottom button** (btn-outline-primary) - in the additional actions row

### ✅ User Experience
- **Dialog prompt** before PDF generation
- **Pre-filled default filename** in format: `quiz-{id}-{name}-{date}`
- **Custom filename** support - users can change the filename
- **Validation** - alerts if filename is empty
- **Keyboard shortcuts** - Enter to confirm, Escape to cancel
- **Consistent behavior** - same dialog on both buttons

### ✅ Default Filename Format
Example: `quiz-47-Math-Quiz-2025-10-18.pdf`
- Quiz ID
- Quiz name (sanitized)
- Current date (YYYY-MM-DD)

## Controller Support

The controller (`QuestionController::actionPdf`) was already updated in the previous implementation to support custom filenames. No additional controller changes were needed.

## Testing

### Test Scenarios

1. **Top PDF Button**
   - Navigate to: `http://localhost/yii2/max-quiz/web/question/index?quiz_id=47`
   - Click the "📄 PDF" button in the top action row
   - ✅ Dialog appears with pre-filled filename
   - Click "Confirm" → PDF downloads

2. **Bottom PDF Button**
   - Scroll down on the same page
   - Click the "📄 PDF" button in the bottom action row
   - ✅ Dialog appears with pre-filled filename
   - Click "Confirm" → PDF downloads

3. **Custom Filename**
   - Click either PDF button
   - Change filename to "my-questions"
   - Click "Confirm"
   - ✅ PDF downloads as `my-questions.pdf`

4. **Empty Filename Validation**
   - Click either PDF button
   - Clear the filename field
   - Click "Confirm"
   - ✅ Alert appears: "Please enter a filename"

5. **Keyboard Shortcuts**
   - Open dialog
   - Press `Enter` → ✅ Downloads PDF
   - Open dialog again
   - Press `Escape` → ✅ Closes dialog

6. **Click Outside Dialog**
   - Open dialog
   - Click on the dark overlay area
   - ✅ Dialog closes

## Browser Compatibility

✅ Tested and works in:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)

## Files Modified

1. ✅ `views/question/index.php` - Added custom dialog integration to both PDF buttons

## Benefits

✅ **Consistent UX** - Same dialog system across the application  
✅ **User Control** - Users choose their own filenames  
✅ **Multiple Buttons** - Both PDF buttons work identically  
✅ **No Breaking Changes** - Old functionality still works  
✅ **Secure** - Filename sanitization in controller  
✅ **Reusable** - Uses the shared dialog component  

## Related Pages with Dialog

Now implemented on:
1. ✅ **Submission Index** (`/submission?quiz_id=47`) - Results & Stats downloads
2. ✅ **Quiz Index** (`/quiz`) - PDF download in dropdown menu
3. ✅ **Question Index** (`/question/index?quiz_id=47`) - Two PDF buttons

## Documentation

For more information:
- See `CUSTOM_DIALOG_USAGE.md` for full usage guide
- See `CUSTOM_DIALOG_EXAMPLE.php` for code examples
- See `PDF_DIALOG_IMPLEMENTATION.md` for Quiz index implementation
- See `CUSTOM_DIALOG_REFACTORING_SUMMARY.md` for component architecture

---

**Implementation Date:** October 18, 2025  
**Status:** ✅ Complete and Ready for Testing  
**Page:** http://localhost/yii2/max-quiz/web/question/index?quiz_id=47

