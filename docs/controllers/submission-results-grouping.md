# Submission Results Grouping

## Purpose

Document how `/submission?quiz_id=...` chooses which quiz submissions to show together, and how the `Results` and `Stats` exports use the same grouping rule.

## Location

- Source: `controllers/SubmissionController.php`
- Related view: `views/submission/index.php`
- Related search model: `models/SubmissionSearch.php`

## Inputs / Outputs

- Input:
  - `quiz_id` query parameter on `/submission`
  - `quiz_id` route parameter for `submission/export` and `submission/export-stats`
- Output:
  - A GridView of submission attempts
  - Grouped CSV exports for results and question stats

## Flow / Behavior

1. The selected quiz is loaded from `quiz_id`.
2. The page always keeps the selected quiz as the anchor quiz.
3. Additional quizzes are included only when all of these are true:
   - `quiz_group` matches after trimming whitespace
   - `name` matches after trimming whitespace
   - the only intended differing field is `language`
4. If `quiz_group` is empty, the page stays in single-quiz mode.
5. If more than one quiz survives the filter:
   - the results page shows all matching submissions together
   - the `Lang` column becomes visible
   - the `Results` and `Stats` exports use the same filtered quiz set
6. If only one quiz survives the filter:
   - the page behaves like a normal single-quiz results page
   - the `Lang` column stays hidden

## Grouping Rule

The grouping rule is intentionally strict.

- When `trim(quiz_group)` is empty, then no grouping is performed.
- When `trim(quiz_group)` is non-empty, then grouping requires:
  - `trim(quiz_group)` equality
  - `trim(name)` equality

This means quizzes such as `C25-B7.01` and `C25-B7.2` must not be merged, even if they share the same `quiz_group`.

## Aggregation Semantics

The submission page does not merge attempts into one row per student.

- When viewing grouped quizzes, then the page aggregates the result set across multiple quiz IDs.
- When rows are rendered, then each row is still one concrete submission attempt.
- When `Score` is shown for a finished submission, then it uses `no_correct / no_questions`.
- When `Score` is shown for an unfinished submission, then it uses `no_correct / no_answered`.

## Exports

### Results export

- Uses the same filtered quiz ID set as the page.
- Includes:
  - `Quizgroep`
  - `Cursus`
  - `Lang`
  - student and scoring fields

### Stats export

- Uses the same filtered quiz ID set as the page.
- Aggregates log rows only for those matching quiz IDs.
- Includes:
  - `Quizgroep`
  - `Cursus`
  - `Lang`
  - date/question statistics

## Edge Cases / Failure Modes

- When `quiz_id` does not resolve to a quiz, then the page falls back to an empty quiz label and exports should fail with `NotFoundHttpException`.
- When `language` is null or empty, then grouping still works and the UI/export should tolerate a blank language.
- When quiz names differ only by surrounding spaces, then they are treated as the same name.
- When quizzes share `quiz_group` but not trimmed `name`, then they must remain separate.

## Related Files

- `models/Quiz.php`
- `models/SubmissionSearch.php`
- `views/submission/index.php`
