## Overview Component – Technical Notes

This directory powers the “Quiz Overview” dashboard that aggregates active quizzes, latest/best student submissions and CSV exports. It is a standalone PHP page that pulls data from the main quiz schema and renders a sortable HTML table with supporting JS/CSS.

### Files at a Glance

- `index.php` – Entry point. Reads DB credentials, fetches quizzes/submissions, prepares CSV payload, and renders HTML.
- `assets/styles.css` – Styling for the dashboard, sticky table headers/columns, status tones and repeat-attempt cues.
- `assets/app.js` – Client behavior: column sorting, CSV export, sort indicators.

### Data Flow

1. **Configuration**  
   `index.php` loads `config/db.php` (two levels up) for DSN, username and password, then opens a PDO connection (exceptions enabled).

2. **Active quizzes**  
   Fetches `id`, `name`, `no_questions` for quizzes where `active = 1`, sorted by `name`.  
   Each quiz name is split at the first period (`.`) into `main` and `sub` labels so the UI can group columns (e.g. `5A.Quiz1` → group `5A`, column label `Quiz1`).

3. **Submission aggregation**  
   - Loads all submissions for the active quiz IDs, ordered by `student_nr`, `quiz_id`, `sort_time DESC` (end time fallback), `id DESC`.  
   - Normalizes each student to `student_nr`, trimmed full name and formatted class (helper `formatClass()` converts “5a” → “5A” or returns a dash).  
   - Calculates per-submission ratio `no_correct / no_questions`. For each `(student, quiz)` pair, only the highest ratio is stored in `$scores`.  
   - Tracks `$attemptCounts[$studentNr][$quizId]` to know how many submissions existed; the best score cell shows a dotted underline and `title="Best of N attempts"` when `N > 1`.

4. **Rendering**  
   - If there is a database error, no active quizzes, or no submissions, a friendly state panel is shown instead of the table.  
   - Otherwise, the table body iterates through sorted students, outputting each quiz score cell with tonal classes (`pass/warn/fail/empty`) and a trailing average column.
   - As rows render, a parallel `$csvRows` array is built for export; headers are `Student, Class, Name, quiz columns…, Average`.

5. **Client JS hooks**  
   `window.quizExport` is injected with `headers` and `rows`. `assets/app.js` reads this global to:
   - Prompt for a separator (`,` default, accepts `;`), escape values, and trigger a client-side CSV download when “Export CSV” is clicked.
   - Add click-based sorting to any header button with `data-sort-index`. Sorting is client-side only and does not refresh the page.

### Styling Cues

- Sticky first three columns (`student`, `class`, `name`) and the final `average` column improve horizontal scrolling.
- Score cells adopt themed backgrounds based on thresholds (≥80 pass, ≥50 warn, else fail, empty otherwise).
- Cells derived from multiple attempts (`.score.repeat`) get a blue bottom accent, hidden side borders, dotted underline on the value, and `cursor: help`.
- Hovering table rows lightly highlights the entire row for readability.

### Extending / Customizing

- **Changing thresholds** – adjust the `if ($percent >= …)` logic near the score rendering block plus the CSS colors if needed.
- **Additional metrics** – compute and push new columns to `$flatQuizzes` or append summary columns after the average;
  remember to mirror those columns in `window.quizExport`.
- **Backend filters** – add `WHERE` conditions to the quiz or submission queries and include matching UI controls (e.g., by class or date range).
- **Localization** – wrap textual strings (empty state messages, prompts) with your localization layer; currently they are English literals.
- **Security** – ensure `config/db.php` is not web-accessible and that the overview page is protected (e.g., behind authentication) since it exposes student data.

### Troubleshooting

- No data appearing? Verify `quiz.active = 1` and submissions exist with non-empty `student_nr`.  
- Incorrect averages? Confirm `no_questions` is populated for each quiz and not zero.  
- CSV export blank? Ensure `window.quizExport` is present (check browser console) and that `headers` contain at least one quiz column.

This README should give new contributors enough context to modify or extend the overview without re-reading the entire source.

