# Max-Quiz

Max-Quiz is a Yii2-based classroom assessment platform. Teachers can build, activate, and monitor quizzes while students interact through a streamlined password-protected interface.

## Highlights

- Teacher dashboard to activate quizzes, monitor submissions, and export results
- Student screen that always serves the next unanswered question and enforces time/IP limits
- Question bank management with quick linking, bulk import/export, and printable previews
- Progress monitoring to adjust scores or force-finish attempts
- Dialog/PDF utilities (see `docs/`) for sharing question sets
- `archived` flag to hide old quizzes without deleting them

## Requirements

- PHP 7.4+ with intl, mbstring, PDO/MySQL, and GD extensions
- Composer 2.x
- MySQL or MariaDB 10.4+
- Web server (Apache/Nginx) or PHP CLI for local serving

## Installation

1. **Clone**

   ```bash
   git clone https://github.com/maxbis/max-quiz.git
   cd max-quiz
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

   Composer runs the Yii post-install hook that sets permissions and generates the cookie validation key.

3. **Configure the database**

   Edit `config/db.php` with your host, database name, username, and password.

4. **Import a schema**

   Choose one of the bundled SQL dumps:

   - `max-quiz-demo.sql` – schema plus demo data (sample quiz, questions, admin user).
   - `max-quiz-DB-structure.sql` – schema only if you want a clean slate.

   Both dumps already include the `archived` column and create the `max-quiz` database by default. Feel free to edit the file before importing if you prefer another name. You can also apply SQL migrations manually from `migrations/` (e.g., `migrations/add_archived_to_quiz.sql`).

5. **Ensure writable directories**

   Verify that `runtime/` and `web/assets/` are writable by the web server user. Create any missing directories if you deploy to a clean server.

## Running Locally

- **PHP built-in server**

  ```bash
  php yii serve --port=8080
  ```

  Visit http://localhost:8080 for the student view and http://localhost:8080/admin for the admin console. All routes are relative to the `web/` directory.

- **Apache/Nginx**

  Point the virtual host root to `web/` and ensure `index.php` is the front controller.

## Default Credentials

If you imported `max-quiz-demo.sql`, the demo admin account is:

- Username: `admin`
- Password: `admin`

The password is stored as a SHA1 hash in the dump—change it immediately on production systems.

## Project Layout

- `web/` – public entry point, assets, and route endpoints
- `docs/` – supplementary guides (PDF dialogs, auto-activation workflows, etc.)
- `migrations/` – raw SQL migrations for manual schema changes
- `max-quiz-demo.sql` / `max-quiz-DB-structure.sql` – database dumps kept in sync with the current schema

## Documentation

Additional feature guides live under `docs/` (for example `docs/QUESTION_INDEX_PDF_DIALOG.md`, `docs/PDF_DIALOG_IMPLEMENTATION.md`, and `docs/TEST_QUIZ_AUTO_ACTIVATE.md`). Refer to them for advanced workflows like PDF exports or automatic quiz activation.

## Contributing

1. Keep both SQL dumps aligned with any schema change.
2. Document new features in this README or in `docs/`.
3. Submit pull requests or issues via GitHub.

For Yii-specific help, consult the [Yii 2 Guide](https://www.yiiframework.com/doc/guide/2.0/en/start-installation).

