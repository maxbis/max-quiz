# Button Style Guide - Max Quiz Application

## Overview
All button styles have been standardized across the application using a centralized CSS file (`web/css/buttons.css`). This ensures consistency, better UX, and easier maintenance.

## Button Sizes

### 1. Primary Buttons - `.quiz-button`
**When to use:** Main page actions, primary CTAs (Create, Save, Submit, etc.)

**Specifications:**
- Font size: 14px
- Padding: 8px 16px
- Min-width: 80px
- Border-radius: 4px

**Example usage:**
```php
<?= Html::a('➕ New Quiz', ['create'], ['class' => 'btn btn-outline-success quiz-button']) ?>
<?= Html::a('💾 Save', ['update'], ['class' => 'btn btn-outline-primary quiz-button']) ?>
```

### 2. Secondary Buttons - `.quiz-button-small`
**When to use:** Table row actions, toolbar utilities, dropdown actions

**Specifications:**
- Font size: 12px
- Padding: 4px 10px
- Min-width: 60px
- Border-radius: 3px

**Example usage:**
```php
<?= Html::a('✏️ Edit', ['/quiz/update', 'id' => $quiz['id']], ['class' => 'btn btn-outline-primary quiz-button-small']) ?>
<?= Html::a('❓ Questions', ['question/index', 'quiz_id' => $quiz['id']], ['class' => 'btn btn-outline-secondary quiz-button-small']) ?>
```

## Color Variants (Bootstrap)

### Primary (Blue) - Main Actions
```php
'class' => 'btn btn-outline-primary quiz-button'
```
Use for: Edit, Update, Primary navigation

### Success (Green) - Create/Confirm Actions
```php
'class' => 'btn btn-outline-success quiz-button'
```
Use for: Create, Save, Confirm, Accept

### Danger (Red) - Destructive Actions
```php
'class' => 'btn btn-outline-danger quiz-button'
```
Use for: Delete, Remove, Warning actions

### Secondary (Gray) - Utility Actions
```php
'class' => 'btn btn-outline-secondary quiz-button'
```
Use for: Export, Import, Utilities, Less important actions

### Dark - Alternative Actions
```php
'class' => 'btn btn-outline-dark quiz-button'
```
Use for: Results, Stats, Alternative navigation

## Special Purpose Buttons

### Modal Buttons
```php
<button class="btn-modal btn-modal-primary">OK</button>
<button class="btn-modal btn-modal-secondary">Cancel</button>
```

### Form Submit Button
```php
<button type="submit" class="btn-submit">Save Changes</button>
```

### Back/Cancel Button
```php
<?= Html::a('Cancel', ['index'], ['class' => 'btn-back']) ?>
```

### Sort Labels Button
```php
<button type="button" class="btn-sort-labels">🔤 Sort by Labels</button>
```

## Visual Hierarchy Examples

### Page Header Actions (Primary)
```php
<?= Html::a('➕ New Quiz', ['create'], ['class' => 'btn btn-outline-success quiz-button']) ?>
<?= Html::a('📥 Import', ['import'], ['class' => 'btn btn-outline-secondary quiz-button']) ?>
```

### Table Row Actions (Small)
```php
<?= Html::a('✏️ Edit', ['update', 'id' => $id], ['class' => 'btn btn-outline-primary quiz-button-small']) ?>
<?= Html::a('🗑️ Delete', ['delete', 'id' => $id], ['class' => 'btn btn-outline-danger quiz-button-small']) ?>
```

### Toolbar Actions (Small)
```php
<?= Html::a('📄 PDF', ['pdf', 'quiz_id' => $id], ['class' => 'btn btn-outline-secondary quiz-button-small']) ?>
<?= Html::a('📊 Results', ['results', 'quiz_id' => $id], ['class' => 'btn btn-outline-dark quiz-button-small']) ?>
```

## Files Modified

### New File Created
- ✅ `web/css/buttons.css` - Centralized button styling

### Files Updated (Removed duplicate CSS)
1. ✅ `assets/AppAsset.php` - Added buttons.css to application
2. ✅ `views/question/index.php`
3. ✅ `views/quiz/index2.php`
4. ✅ `views/quiz/edit-labels.php`
5. ✅ `views/submission/index.php`
6. ✅ `views/question/export.php`
7. ✅ `views/question/import.php`
8. ✅ `views/question/_form.php`
9. ✅ `views/question/list.php`
10. ✅ `views/question/list-blind.php`
11. ✅ `views/question/multipleUpdate.php`
12. ✅ `views/question/indexraw.php`
13. ✅ `views/quiz/index.php`
14. ✅ `views/quiz/_form.php`
15. ✅ `views/quiz/part_quiz_details.php`
16. ✅ `views/submission/_form.php`
17. ✅ `views/site/question.php`

## Benefits

✅ **Consistency** - Same button looks identical everywhere
✅ **Visual Hierarchy** - Two clear sizes show importance
✅ **Maintainability** - Change once in buttons.css, updates everywhere
✅ **Professional Look** - Modern, clean interface
✅ **Better UX** - Users instantly recognize action importance
✅ **Responsive** - Automatically adjusts on mobile devices

## Responsive Design

On screens smaller than 768px:
- `.quiz-button`: 13px font, 6px 12px padding
- `.quiz-button-small`: 11px font, 3px 8px padding

## Best Practices

1. **Use `.quiz-button` for:**
   - Page-level actions (Create New, Save All)
   - Primary CTAs
   - Form submissions
   - Navigation between major sections

2. **Use `.quiz-button-small` for:**
   - Table row actions (Edit, Delete, View)
   - Dropdown menu triggers
   - Toolbar utilities (Export, PDF, Import)
   - Secondary navigation

3. **Color selection:**
   - Primary (Blue): Default actions, edits
   - Success (Green): Creates, confirmations
   - Danger (Red): Deletes, warnings
   - Secondary (Gray): Utilities, less important
   - Dark: Special cases (Results, Stats)

4. **Consistency:**
   - Always use the same color for the same action type
   - Example: All "Edit" buttons should be `btn-outline-primary`
   - Example: All "Delete" buttons should be `btn-outline-danger`

## Need to Add a New Button Style?

Edit `web/css/buttons.css` - changes will apply application-wide automatically!

