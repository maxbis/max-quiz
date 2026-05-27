# Classic Quiz Flow Regression Test

## Purpose

This regression test proves that the original self-paced quiz flow still works after the live quiz / Kahoot-style module was added.

It is intended to catch accidental breakage in the legacy student flow:

- starting a normal quiz from the classic start page
- answering questions through the original `site/answer` route
- finishing the quiz
- opening the classic results page
- confirming classic `log` rows are not linked to any live session

## Location

- Test file: [tests/functional/ClassicQuizFlowCest.php](/Users/maxbisschop/dev/www/max-quiz/tests/functional/ClassicQuizFlowCest.php)
- Related config:
  - [config/test.php](/Users/maxbisschop/dev/www/max-quiz/config/test.php)
  - [config/test_db.php](/Users/maxbisschop/dev/www/max-quiz/config/test_db.php)

## Inputs/Outputs

Inputs:

- a database that contains an active classic quiz named `Test`
- that quiz must use code `test`
- the quiz must have active linked questions

Output:

- one passing Codeception functional test
- a completed classic quiz submission created during the test
- automatic cleanup of the test submission and its `log` rows after the test finishes

## Flow / Behavior

The regression test does the following:

1. Loads the active classic quiz with:
   - `name = Test`
   - `password = test`
   - `active = 1`
   - `archived = 0`
2. Opens the original quiz start page at `submission/create`.
3. Submits the classic start form with test student data.
4. Confirms the old flow redirects into the classic question route.
5. Reads the created `submission` row and its `question_order`.
6. Looks up the correct answer for each question from the `question` table.
7. Posts answers through the original `site/answer` route until the quiz finishes.
8. Opens the classic results page.
9. Verifies:
   - the submission is marked finished
   - all questions were answered
   - all answers were scored correctly
   - all created `log` rows have:
     - `live_session_id = null`
     - `live_session_question_id = null`

## Edge Cases / Failure Modes

- When the `Test` quiz is missing or inactive, then the test fails immediately.
- When the classic start flow depends on localhost/IP behavior, then the test sets `REMOTE_ADDR` to `127.0.0.1`.
- When the test is run against a shared database, then it deletes only the submission rows it created for:
  - `first_name = Regression`
  - `last_name = ClassicFlow`
  - `class = 9A`

## How To Run

Run only this regression test:

```bash
TEST_DB_DSN='mysql:host=localhost;dbname=max-quiz' vendor/bin/codecept run functional ClassicQuizFlowCest
```

If you want to use a dedicated test database instead, change `TEST_DB_DSN` accordingly.

## Related Files

- [controllers/SubmissionController.php](/Users/maxbisschop/dev/www/max-quiz/controllers/SubmissionController.php)
- [controllers/SiteController.php](/Users/maxbisschop/dev/www/max-quiz/controllers/SiteController.php)
- [controllers/MyHelpers.php](/Users/maxbisschop/dev/www/max-quiz/controllers/MyHelpers.php)
- [views/submission/start.php](/Users/maxbisschop/dev/www/max-quiz/views/submission/start.php)
- [views/site/question.php](/Users/maxbisschop/dev/www/max-quiz/views/site/question.php)
- [views/site/results.php](/Users/maxbisschop/dev/www/max-quiz/views/site/results.php)
