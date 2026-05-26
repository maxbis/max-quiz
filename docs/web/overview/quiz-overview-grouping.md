# Quiz Overview Grouping

## Purpose

Describe how the standalone quiz overview in `web/overview/` groups active quizzes into columns and aggregates student scores.

## Location

- Source: `web/overview/index.php`
- Related assets: `web/overview/assets/app.js`, `web/overview/assets/styles.css`

## Inputs / Outputs

- Input:
  - active quiz rows from `quiz`
  - submission rows from `submission`
- Output:
  - one overview column per logical quiz grouping
  - a CSV export with the same grouped columns

## Flow / Behavior

1. Load all active quizzes with `id`, `name`, `quiz_group`, `language`, and `no_questions`.
2. Build one logical grouping key per quiz.
3. Fetch submissions for all active quiz IDs.
4. Map each submission back to its logical grouped quiz key.
5. For each `(student, grouped quiz)` pair, keep the best score ratio.
6. Count attempts per `(student, grouped quiz)` so repeat attempts can still be indicated.
7. Render one grouped column per logical quiz and one trailing average column.

## Grouping Rule

The overview groups quizzes only when both of these values match after trimming:

- `quiz_group`
- `name`

When `quiz_group` is empty:

- the overview falls back to a single-quiz key based on the quiz ID
- unrelated quizzes are not merged accidentally

This means language variants of the same quiz can collapse together, while different quizzes inside the same group remain separate.

## Labels

- The grouped column header uses the trimmed quiz `name`.
- The grouped sub-label uses the distinct language codes joined with ` / `.
- The full tooltip keeps the original quiz names for traceability.

## Aggregation Semantics

- When multiple language variants belong to the same grouped quiz, then student scores are compared within that grouped set.
- When a student has multiple attempts inside that grouped set, then the best score ratio is kept.
- When multiple attempts exist, then the cell still shows the repeat-attempt cue and tooltip.
- Column averages are calculated from the grouped best scores, not from raw attempts.

## Edge Cases / Failure Modes

- When `language` is empty, the grouped column still works and may show a generic grouped sub-label.
- When `quiz_group` matches but trimmed `name` differs, quizzes must render as separate columns.
- When no submissions exist, the page shows the empty state instead of a table.
- When DB access fails, the page shows the database error state.

## Related Files

- `controllers/SubmissionController.php`
- `models/Quiz.php`
- `web/overview/README.md`
