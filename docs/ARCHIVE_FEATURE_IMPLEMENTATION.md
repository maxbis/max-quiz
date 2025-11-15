# Quiz Archive Feature - Implementation Summary

## Overview
Implemented a quiz archiving system that allows you to hide old quizzes from the main list without permanently deleting them. This keeps your quiz list manageable while preserving historical data.

## What Was Implemented

### 1. Database Changes
- **New Column**: Added `archived` column to the `quiz` table
  - Type: TINYINT(1) 
  - Default: 0 (not archived)
  - Values: 0 = active, 1 = archived
  - Added index for better query performance

### 2. Model Updates
**Quiz Model** (`models/Quiz.php`)
- Added `archived` property
- Updated validation rules to include archived field
- Set default value to 0
- Added attribute label

### 3. Controller Updates
**QuizController** (`controllers/QuizController.php`)

#### Modified `actionIndex()`
- Added `$show` parameter with 3 options:
  - `'active'` (default) - Shows only non-archived quizzes
  - `'archived'` - Shows only archived quizzes
  - `'all'` - Shows all quizzes regardless of archive status
- Passes `$showFilter` to the view

#### New `actionToggleArchive($id)`
- Toggles the archive status of a quiz
- When archiving: automatically deactivates the quiz
- Shows success/error flash messages
- Redirects back to quiz list

### 4. View Updates
**Quiz List View** (`views/quiz/index2.php`)

#### Filter Buttons
Added filter buttons at the top:
- **Active Quizzes** - Shows only active (non-archived) quizzes (default)
- **Archived Quizzes** - Shows only archived quizzes
- **All Quizzes** - Shows everything

#### Visual Indicators
- **Archived Badge**: Shows "ARCHIVED" badge next to archived quiz names
- **Faded Appearance**: Archived quizzes appear with reduced opacity (60%)
- **Disabled Checkbox**: Active checkbox is disabled for archived quizzes
- **Hover Effect**: Slightly increases opacity on hover for better readability

#### Actions Menu
Added to the "More" dropdown:
- **📦 Archive** button (for active quizzes) - Archives the quiz
- **📤 Restore** button (for archived quizzes) - Restores from archive
- Confirmation dialog before archiving/restoring

### 5. CSS Styling
Added custom styles for:
- Filter button bar
- Archived quiz row styling
- Archive badge styling
- Hover effects

## How to Use

### To Archive a Quiz:
1. Go to the Quiz List page
2. Find the quiz you want to archive
3. Click the "⋮ More" button in the Actions column
4. Click "📦 Archive"
5. Confirm the action
6. The quiz will be archived and disappear from the active list

### To View Archived Quizzes:
1. Click the "Archived Quizzes" button at the top of the quiz list
2. All archived quizzes will be displayed with a faded appearance

### To Restore an Archived Quiz:
1. Click "Archived Quizzes" to view archived quizzes
2. Find the quiz you want to restore
3. Click "⋮ More" → "📤 Restore"
4. Confirm the action
5. The quiz will be restored to the active list

### To View All Quizzes:
- Click the "All Quizzes" button to see both active and archived quizzes together

## Database Migration

**IMPORTANT**: You need to run the SQL migration to add the `archived` column to your database.

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your `max-quiz` database
3. Go to the SQL tab
4. Copy and paste the contents of `migrations/add_archived_to_quiz.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
# Navigate to your project directory
cd d:\www\yii2\max-quiz

# Run the migration (adjust path to mysql.exe if needed)
mysql -u root -p max-quiz < migrations/add_archived_to_quiz.sql
```

### Option 3: Using Docker (if applicable)
```bash
docker exec -i max-quiz-db mysql -u root -p max-quiz < migrations/add_archived_to_quiz.sql
```

## Key Features

✅ **Non-Destructive**: Archiving doesn't delete any data
✅ **Reversible**: Easily restore archived quizzes
✅ **Clean UI**: Keeps your main quiz list focused on active quizzes
✅ **Visual Feedback**: Clear visual indicators for archived status
✅ **Auto-Deactivate**: Archived quizzes are automatically deactivated
✅ **Filtered Views**: Switch between active, archived, or all quizzes
✅ **Confirmation Dialogs**: Prevents accidental archiving

## Files Modified

1. `migrations/add_archived_to_quiz.sql` - NEW: Database migration
2. `models/Quiz.php` - Added archived attribute
3. `controllers/QuizController.php` - Added filtering and toggle action
4. `views/quiz/index2.php` - Added UI for filtering and archiving
5. `max-quiz-DB-structure.sql` - Updated schema documentation

## Notes

- Archived quizzes cannot be activated (checkbox is disabled)
- When you archive a quiz, it's automatically deactivated
- Default view shows only active quizzes
- All quiz data, questions, and submissions are preserved when archived
- You can still view results and questions for archived quizzes

## Future Enhancements (Optional)

Consider these additional features:
- Archive date tracking (when was it archived)
- Bulk archive action (archive multiple quizzes at once)
- Auto-archive based on age (e.g., quizzes older than 6 months)
- Archive reason/notes field
- Archive statistics in dashboard

---
**Implementation Date**: October 16, 2025
**Approach Used**: Simple Boolean Flag (Approach 1)

