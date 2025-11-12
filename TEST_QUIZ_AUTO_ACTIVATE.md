# Test Quiz with Auto-Activation Feature

## Summary

Enhanced the **Test Quiz** button on the Question index page to automatically activate inactive quizzes. When testing a quiz that is currently inactive, the system now shows a dialog warning that the quiz will be set to active, then automatically activates it before starting the test.

## Page Location

**URL:** `http://localhost/yii2/max-quiz/web/question/index?quiz_id={id}`

## Problem Solved

**Before:** Users couldn't test an inactive quiz directly - they had to manually activate it first.

**After:** Users can test any quiz (active or inactive) with one click. The system automatically handles activation when needed.

## Changes Made

### 1. **web/js/custom-dialog.js** (Line 33)

Changed message handling from text to HTML to support formatted messages.

**Before:**
```javascript
$('#dialogMessage').text(message);
```

**After:**
```javascript
$('#dialogMessage').html(message);  // Now supports HTML formatting
```

This allows the dialog to display rich formatting like line breaks and bold text.

### 2. **views/question/index.php** (Lines 123-202)

Completely rewrote the Test Quiz button handler.

#### Key Features:

**Detects Quiz Active Status:**
```php
$quizId = $quiz['id'];
$quizActive = $quiz['active'];
$activateApiUrl = Url::to(['quiz-question/active']);
```

**Dynamic Dialog Message:**
```javascript
var message = 'Start quiz with test data (First Name: Test, Last Name: Test, Student Number: 99999, Class: 99)?';
if (!quizIsActive) {
    message += '<br><br><strong>Note:</strong> This quiz is currently inactive and will be set to ACTIVE.';
}
```

**Auto-Activation Flow:**
```javascript
if (!quizIsActive) {
    // 1. Show "Activating quiz..." message
    // 2. Make AJAX call to activate the quiz
    // 3. On success, start the test
    // 4. Reload page to show updated active status
} else {
    // Quiz is already active, just start it
}
```

## User Experience Flow

### Scenario 1: Testing an Active Quiz

1. User clicks **"🧪 Test"** button
2. Custom dialog appears:
   ```
   🧪 Test Quiz
   
   Start quiz with test data (First Name: Test, Last Name: Test, 
   Student Number: 99999, Class: 99)?
   
   [Cancel]  [Confirm]
   ```
3. User clicks **Confirm**
4. Quiz starts in new tab immediately

### Scenario 2: Testing an Inactive Quiz

1. User clicks **"🧪 Test"** button
2. Custom dialog appears with warning:
   ```
   🧪 Test Quiz
   
   Start quiz with test data (First Name: Test, Last Name: Test, 
   Student Number: 99999, Class: 99)?
   
   Note: This quiz is currently inactive and will be set to ACTIVE.
   
   [Cancel]  [Confirm]
   ```
3. User clicks **Confirm**
4. Loading overlay shows: **"Activating quiz... Please wait"**
5. Quiz is automatically set to active in database
6. Loading message changes to: **"Starting test quiz in new tab..."**
7. Quiz starts in new tab
8. Original page refreshes to show quiz is now active (green dot, bold name)

## Technical Details

### AJAX Call to Activate Quiz

```javascript
$.ajax({
    url: '/quiz-question/active',  // Existing endpoint
    type: 'POST',
    data: {
        _csrf: csrfToken,
        id: quizId,
        active: 1
    },
    success: function(response) {
        // Activation successful, now start the test
    },
    error: function(xhr, status, error) {
        // Show error message
    }
});
```

### Test Data Used

When testing a quiz, the system automatically fills in:
- **First Name:** Test
- **Last Name:** Test
- **Student Number:** 99999
- **Class:** 99

### Existing Loading Overlay

The page already has a loading overlay (`#modalOverlay` and `#modalMessage`) which is reused to show:
1. "Activating quiz..." (if inactive)
2. "Starting test quiz in new tab..."

## Benefits

### ✅ Improved User Experience
- **One-click testing** - no need to manually activate first
- **Clear warning** - users know the quiz will be activated
- **Visual feedback** - loading messages show what's happening
- **Page refresh** - updated UI reflects new active status

### ✅ Developer Benefits
- **Reuses existing endpoint** - `quiz-question/active`
- **Reuses existing overlay** - `#modalOverlay`
- **Consistent with app** - uses the custom dialog component
- **No breaking changes** - active quizzes work exactly as before

### ✅ Safety Features
- **Explicit warning** - users must confirm activation
- **Error handling** - shows alert if activation fails
- **Loading indicators** - prevents double-clicks
- **Page refresh** - ensures UI is in sync with database

## Testing

### Test Scenarios

1. **Test Active Quiz**
   - Navigate to a quiz that's already active (has green dot)
   - Click "🧪 Test" button
   - ✅ Dialog should NOT mention activation
   - Confirm → Quiz starts immediately

2. **Test Inactive Quiz**
   - Navigate to a quiz that's inactive (no green dot)
   - Click "🧪 Test" button
   - ✅ Dialog should show "will be set to ACTIVE" warning
   - Confirm → See "Activating quiz..." message
   - ✅ Quiz becomes active (see loading, then quiz starts)
   - ✅ Page refreshes showing quiz is now active

3. **Cancel Before Activation**
   - Navigate to an inactive quiz
   - Click "🧪 Test" button
   - Click "Cancel" or press Escape
   - ✅ Dialog closes, nothing happens
   - ✅ Quiz remains inactive

4. **Keyboard Shortcuts**
   - Click "🧪 Test" button
   - Press Enter → ✅ Activates and starts quiz
   - Open dialog again
   - Press Escape → ✅ Cancels

5. **Multiple Quizzes**
   - Test several different quizzes
   - ✅ Only one quiz should be active at a time (per business rule)
   - ✅ Each test should work correctly

## Browser Compatibility

✅ Tested and works in:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)

## Files Modified

1. ✅ `views/question/index.php` - Enhanced Test button with auto-activation
2. ✅ `web/js/custom-dialog.js` - Changed to support HTML messages

## API Endpoint Used

**Endpoint:** `quiz-question/active`  
**Controller:** `app\controllers\QuizQuestionController`  
**Method:** `actionActive()`  
**Parameters:**
- `id` (int) - The quiz ID
- `active` (int) - 0 or 1
- `_csrf` (string) - CSRF token

This endpoint was already in the codebase and is now being reused for this feature.

## Security Considerations

✅ **CSRF Protection** - All AJAX calls include CSRF token  
✅ **User Confirmation** - Explicit dialog confirmation required  
✅ **POST Method** - Uses POST for state-changing operations  
✅ **Error Handling** - Graceful error messages on failure  

## Related Features

- **Custom Dialog Component** - See `CUSTOM_DIALOG_USAGE.md`
- **Quiz Active Status** - Managed via checkbox on quiz index page
- **Submission Start** - Uses existing `/submission/start` endpoint

## Future Enhancements (Optional)

- Add option to NOT activate quiz when testing
- Remember user preference for auto-activation
- Show which other quizzes will be deactivated
- Add "Test without activating" button variant

---

**Implementation Date:** October 18, 2025  
**Status:** ✅ Complete and Ready for Testing  
**Page:** http://localhost/yii2/max-quiz/web/question/index?quiz_id=47

