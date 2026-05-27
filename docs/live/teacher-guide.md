# Live Quiz Teacher Guide

## Purpose

This guide explains how a teacher can run a Live Quiz session in class.

Use Live Quiz when:

- the whole class should answer the same question at the same time
- the teacher wants to control the pace
- the screen should show rankings after each question

## Location

Teacher screens:

- Live Quiz overview: [http://localhost:8080/live/teacher/index](http://localhost:8080/live/teacher/index)
- Session management: [http://localhost:8080/live/teacher/view](http://localhost:8080/live/teacher/view)

Student join page:

- [http://localhost:8080/live/student/index](http://localhost:8080/live/student/index)

Projector / presentation screen:

- `/live/screen/view?code=JOINCODE`

## Before You Start

Make sure:

- you are logged in as a teacher/admin
- the quiz already exists in the normal quiz list
- the quiz has active linked questions
- the quiz you want to use can be found on the Live Quiz page

## Step-by-Step Teacher Flow

### 1. Open Live Quiz

Go to:

- [http://localhost:8080/live/teacher/index](http://localhost:8080/live/teacher/index)

### 2. Find the quiz

In the `Create Session` panel:

- type part of the quiz name in `Search quiz`
- you can use `*` as a wildcard
- select the quiz from the dropdown

If a language is set on the quiz, it is shown in the dropdown label.

### 3. Create the live session

Click:

- `Create Live Session`

This opens the session management page.

### 4. Let students join

On the teacher session page you will see:

- the join code
- a button to open the student join page
- a QR code for the student join page

Students join by:

1. opening the join page
2. entering the join code
3. entering first name, last name, and class

While students join, the `Lobby` section on the teacher page shows who is connected.

### 5. Open the big screen

Use:

- `Open Big Screen`

Put that page on the projector or large classroom display.

The big screen shows:

- session status
- join code
- question number
- leaderboard after each round

### 6. Start the first question

When everyone is ready, click:

- `Open Question 1`

For later rounds, click:

- `Open Next Question`

Students will automatically see the open question on their devices.

### 7. Watch the answer count

During the round, the teacher page shows:

- the current question
- how many answers have been received

Use this to decide when enough students have answered.

### 8. Close the question

Click:

- `Close Question + Show Leaderboard`

The big screen will show:

- top 5
- big movers
- the correct answer

### 9. Explain the question

On the projector screen, the correct answer area is clickable.

When clicked, it shows:

- the full question
- all answer options
- the correct answer highlighted

Use this to explain the question to the class before moving on.

### 10. Continue or finish

Repeat the question cycle until the quiz is complete.

When you are done, click:

- `Finish Session`

## After the Session

Finished sessions are listed on the Live Quiz overview page.

From there you can:

- open them again with `Manage`
- delete finished sessions with `Delete`

Deleting a finished live session removes the live session data and linked live answers, but it does not remove the original quiz itself.

## Practical Notes

- Keep the teacher page open on your own device.
- Keep the big screen open on the projector.
- Use the teacher-page QR code before the quiz starts if you want students to join quickly.
- The big screen no longer shows the QR code; it shows the join code and presentation content only.
- If students join too late, they may miss the lobby phase and need a new session.

## Common Problems

When students cannot join:

- check that the session is still in `Lobby`
- verify the join code
- verify the correct student join page is being used

When answers cannot be submitted:

- check that the question is currently open
- check that the student did not already answer that question

When you want to remove an old session:

- finish it first
- then delete it from the Live Quiz overview

## Related Files

- [docs/live/technical-overview.md](/Users/maxbisschop/dev/www/max-quiz/docs/live/technical-overview.md)
- [live/views/teacher/index.php](/Users/maxbisschop/dev/www/max-quiz/live/views/teacher/index.php)
- [live/views/teacher/view.php](/Users/maxbisschop/dev/www/max-quiz/live/views/teacher/view.php)
- [live/views/screen/view.php](/Users/maxbisschop/dev/www/max-quiz/live/views/screen/view.php)
