# Archive Filter for Quiz Assignments - Implementation Summary

## Overview
Added archive filtering to the "Manage Quiz Assignments" section on the Question Update page. By default, only active (non-archived) quizzes are shown, keeping the list manageable. Users can toggle to show all quizzes including archived ones.

## What Was Implemented

### 1. Controller Updates
**QuestionController.php** (`controllers/QuestionController.php`)

#### Modified `actionUpdate()` method:
- Added `$show_archived` parameter (default: 0)
  - `0` = Show only active (non-archived) quizzes (default)
  - `1` = Show all quizzes including archived
- Updated SQL query to include `q.archived` field
- Added filter: `WHERE q.archived = 0` when `show_archived = 0`
- Passes `show_archived` parameter to the view

**SQL Changes:**
```php
// Before
$sql = "select q.id, q.name, qq.active from quiz q
    left join quizquestion qq on qq.quiz_id = q.id 
    and qq.active = 1 and qq.question_id=$id
    order by q.name ASC";

// After
$archiveFilter = $show_archived ? '' : ' WHERE q.archived = 0';
$sql = "select q.id, q.name, qq.active, q.archived from quiz q
    left join quizquestion qq on qq.quiz_id = q.id 
    and qq.active = 1 and qq.question_id=$id"
    . $archiveFilter .
    " order by q.name ASC";
```

### 2. View Updates

#### **update.php** (`views/question/update.php`)
- Passes `show_archived` parameter to the form

#### **_form.php** (`views/question/_form.php`)

**Toggle Button:**
- Added toggle button next to "Manage Quiz Assignments"
- Shows "📂 Show Archived" when viewing active quizzes only
- Shows "📦 Hide Archived" when viewing all quizzes
- Button preserves all URL parameters when toggling

**Visual Indicators:**
- Archived quizzes show "ARCHIVED" badge next to name
- Archived quizzes appear with reduced opacity (60%)
- Archived items have gray background
- Hover effect slightly increases opacity for better readability

**CSS Styling:**
```css
.quiz-assignment-archived {
    opacity: 0.6;
    background-color: #f5f5f5 !important;
    border-color: #d0d0d0 !important;
}

.quiz-assignment-archived:hover {
    opacity: 0.75;
    background-color: #ececec !important;
}

.quiz-assignment-archived .form-check-label {
    color: #6c757d;
}
```

## How to Use

### Default Behavior (Active Quizzes Only):
1. Go to Question Update page (e.g., `/question/update?id=204`)
2. Click "📋 Manage Quiz Assignments"
3. Only active (non-archived) quizzes are shown
4. This keeps the list clean and focused

### To Show Archived Quizzes:
1. Click the "📂 Show Archived" button
2. All quizzes (active + archived) will be displayed
3. Archived quizzes appear faded with "ARCHIVED" badge
4. You can still assign questions to archived quizzes if needed

### To Hide Archived Quizzes Again:
1. Click the "📦 Hide Archived" button
2. Returns to showing only active quizzes

## Key Features

✅ **Clean Default View**: Shows only active quizzes by default
✅ **Easy Toggle**: One-click toggle to show/hide archived quizzes
✅ **Visual Distinction**: Archived quizzes are clearly marked
✅ **Preserves Functionality**: Can still assign questions to archived quizzes
✅ **Consistent UI**: Matches the archive styling from quiz list page
✅ **URL State**: Toggle state is preserved in URL parameters

## Benefits

1. **Reduced Clutter**: Question update page shows only relevant quizzes
2. **Better UX**: Less scrolling, easier to find active quizzes
3. **Flexibility**: Can still access archived quizzes when needed
4. **Consistent**: Matches the archive behavior on quiz list page

## Files Modified

1. `controllers/QuestionController.php` - Added filtering logic
2. `views/question/update.php` - Pass show_archived parameter
3. `views/question/_form.php` - Added toggle button and visual styling

## Integration with Main Archive Feature

This feature works seamlessly with the main quiz archive feature:
- Quizzes archived on the quiz list page automatically become hidden here
- Restored quizzes automatically appear in the active list
- Same visual styling for consistency

## Technical Notes

- Default behavior: `show_archived = 0` (shows only active quizzes)
- The parameter is passed via URL: `?id=204&show_archived=1`
- SQL filter is applied at the database level for efficiency
- Archived status is fetched from the `quiz.archived` column
- Toggle button preserves other URL parameters (quiz_id, etc.)

---
**Implementation Date**: October 16, 2025
**Related Feature**: Quiz Archive System

