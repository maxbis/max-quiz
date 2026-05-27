# Live Quiz Technical Overview

## Purpose

The Live Quiz module adds a teacher-paced quiz mode alongside the original self-paced quiz flow.

It is designed for classroom use where:

- the teacher controls when each question opens
- students join a shared live session with a code
- answers are collected for the current question only
- the screen can show a leaderboard and explanation view after each round

The module is isolated under `/live/...` so the original quiz routes continue to exist next to it.

## Location

Main source paths:

- [live/Module.php](/Users/maxbisschop/dev/www/max-quiz/live/Module.php)
- [live/controllers/TeacherController.php](/Users/maxbisschop/dev/www/max-quiz/live/controllers/TeacherController.php)
- [live/controllers/StudentController.php](/Users/maxbisschop/dev/www/max-quiz/live/controllers/StudentController.php)
- [live/controllers/ScreenController.php](/Users/maxbisschop/dev/www/max-quiz/live/controllers/ScreenController.php)
- [live/services/LiveSessionManager.php](/Users/maxbisschop/dev/www/max-quiz/live/services/LiveSessionManager.php)
- [live/services/LiveLeaderboardService.php](/Users/maxbisschop/dev/www/max-quiz/live/services/LiveLeaderboardService.php)
- [live/views/teacher/](/Users/maxbisschop/dev/www/max-quiz/live/views/teacher)
- [live/views/student/](/Users/maxbisschop/dev/www/max-quiz/live/views/student)
- [live/views/screen/](/Users/maxbisschop/dev/www/max-quiz/live/views/screen)

Schema and bootstrap:

- [migrations/m260527_120000_add_live_quiz_tables.php](/Users/maxbisschop/dev/www/max-quiz/migrations/m260527_120000_add_live_quiz_tables.php)
- [config/web.php](/Users/maxbisschop/dev/www/max-quiz/config/web.php)

Regression coverage for the classic quiz flow:

- [tests/functional/ClassicQuizFlowCest.php](/Users/maxbisschop/dev/www/max-quiz/tests/functional/ClassicQuizFlowCest.php)

## Inputs / Outputs

Inputs:

- an existing quiz from the `quiz` table
- linked questions from `quizquestion`
- student details submitted through the live join form
- teacher actions that change session state

Outputs:

- live session records and ordered session questions
- per-student live submissions
- per-question answer logs
- leaderboard snapshots and mover deltas
- teacher, student, and projector-facing live views

## Flow / Behavior

### Module bootstrap

The module is registered in [config/web.php](/Users/maxbisschop/dev/www/max-quiz/config/web.php) under the `live` key.

That exposes routes such as:

- `/live/teacher/index`
- `/live/teacher/view?id=...`
- `/live/student/index`
- `/live/student/play?code=...`
- `/live/screen/view?code=...`

### Teacher flow

The teacher uses [TeacherController.php](/Users/maxbisschop/dev/www/max-quiz/live/controllers/TeacherController.php).

Main behavior:

1. The teacher creates a live session from an existing quiz.
2. The system copies the quiz question order into `live_session_question`.
3. The session starts in `lobby` status.
4. The teacher opens the next question.
5. The teacher closes the question and moves to `leaderboard`.
6. The teacher repeats until the session is finished.

When a session is `finished`, it can also be deleted from the teacher overview.

### Student flow

The student uses [StudentController.php](/Users/maxbisschop/dev/www/max-quiz/live/controllers/StudentController.php).

Main behavior:

1. The student opens the join page.
2. The student enters the join code, first name, last name, and class.
3. A normal `submission` row is created for that student.
4. A linking row is created in `live_session_submission`.
5. The student waits in the lobby until a question opens.
6. The student screen polls for the current session state.
7. The student can answer only the currently open question.
8. Duplicate answers for the same live question are rejected.

### Screen flow

The projector / big-screen view uses [ScreenController.php](/Users/maxbisschop/dev/www/max-quiz/live/controllers/ScreenController.php).

Main behavior:

- in `lobby`, it shows the join code and waiting state
- in `question_open`, it shows the current question and answer count
- in `leaderboard`, it shows top 5, big movers, and the correct answer
- clicking the correct answer opens an explanation overlay with all answer options

### Leaderboard logic

The leaderboard is built in [LiveLeaderboardService.php](/Users/maxbisschop/dev/www/max-quiz/live/services/LiveLeaderboardService.php).

Ranking is based on:

1. highest total correct answers
2. earliest latest-correct timestamp
3. lowest submission id as final stable fallback

Snapshots are stored after a question closes so the module can show:

- top 5
- previous rank
- positive rank movement

## Data Model

The module reuses existing tables:

- `quiz`
- `question`
- `quizquestion`
- `submission`
- `log`

It adds these tables:

- `live_session`
- `live_session_question`
- `live_session_submission`
- `live_session_rank_snapshot`

It also adds nullable columns to `log`:

- `live_session_id`
- `live_session_question_id`

When the old self-paced flow writes to `log`, those live columns remain `null`.

## Edge Cases / Failure Modes

- When a session has no active questions, session creation fails.
- When a student joins after the lobby is closed, the join request is rejected.
- When a question is not open, answer submission is rejected.
- When a student already answered the active live question, duplicate submission is rejected.
- When a teacher deletes a finished session, live session data and linked live submissions are removed, but the original quiz definition stays in place.
- When the classic flow is regression-tested outside normal web entry conditions, legacy path/IP assumptions can surface; the regression test and recent fixes cover those cases.

## Related Files

- [docs/live/teacher-guide.md](/Users/maxbisschop/dev/www/max-quiz/docs/live/teacher-guide.md)
- [docs/tests/functional/classic-quiz-flow-regression.md](/Users/maxbisschop/dev/www/max-quiz/docs/tests/functional/classic-quiz-flow-regression.md)
- [README.md](/Users/maxbisschop/dev/www/max-quiz/README.md)
