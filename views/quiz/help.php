<?php

$this->title = 'Quiz Help';
?>

<style>
    .quiz-help-page {
        max-width: 1160px;
        margin: 0 auto 40px;
    }

    .quiz-help-hero {
        border: 1px solid #dbe4ef;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 28px;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 26%),
            radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.10), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.05);
    }

    .quiz-help-hero h1 {
        margin: 0 0 10px;
        font-size: 2.6rem;
        letter-spacing: -0.04em;
    }

    .quiz-help-hero p {
        margin: 0;
        color: #475569;
        font-size: 1.02rem;
    }

    .quiz-help-section {
        border: 1px solid #dbe4ef;
        border-radius: 24px;
        padding: 24px 26px;
        margin-bottom: 20px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
    }

    .quiz-help-section h2 {
        margin: 0 0 14px;
        font-size: 1.85rem;
        letter-spacing: -0.03em;
    }

    .quiz-help-section p {
        color: #334155;
        line-height: 1.6;
    }

    .quiz-help-section ul,
    .quiz-help-section ol {
        color: #334155;
        line-height: 1.7;
        padding-left: 22px;
    }

    .quiz-help-note {
        border-left: 4px solid #0ea5e9;
        background: #f0f9ff;
        border-radius: 12px;
        padding: 12px 14px;
        margin: 14px 0;
        color: #0f172a;
    }
</style>

<div class="quiz-help-page">
    <section class="quiz-help-hero">
        <h1>Quiz Help</h1>
        <p>This page explains the main quiz admin workflows in a simple reference format.</p>
    </section>

    <section class="quiz-help-section" id="create-quiz">
        <h2>Create a new quiz</h2>
        <p><strong>Use case:</strong> You are preparing a brand-new test, practice quiz, or resit and there is no existing quiz that is close enough to copy.</p>
        <ol>
            <li>Open the quiz list.</li>
            <li>Click <strong>New Quiz</strong>.</li>
            <li>Fill in the name, quiz group, code, and settings.</li>
            <li>Save the quiz.</li>
            <li>After saving, add or activate the questions that should belong to the quiz.</li>
        </ol>
    </section>

    <section class="quiz-help-section" id="copy-quiz">
        <h2>Copy a quiz</h2>
        <p><strong>Use case:</strong> You want to reuse last period's quiz as a starting point for a new class, new version, or corrected edition.</p>
        <p>Use copy when you want a new version of an existing quiz without rebuilding it from scratch.</p>
        <ol>
            <li>Find the original quiz in the quiz list.</li>
            <li>Open its action menu.</li>
            <li>Choose <strong>Copy</strong>.</li>
            <li>Rename the new quiz, change the code, and review the linked questions.</li>
        </ol>
    </section>

    <section class="quiz-help-section" id="change-question-order">
        <h2>Change the order of questions in a quiz</h2>
        <p><strong>Use case:</strong> You want easier questions first, grouped topics together, or a fixed order that matches a lesson plan or printed handout.</p>
        <p>The quiz order is determined by the active quiz-question links. When a normal quiz starts, the student question order is generated from those links.</p>
        <ol>
            <li>Open the quiz question view for the quiz.</li>
            <li>Adjust the question order values or rearrange the linked questions.</li>
            <li>Save the changes.</li>
            <li>Test the quiz if the order is important.</li>
        </ol>
        <div class="quiz-help-note">If the quiz has random mode enabled, the stored student order can still be shuffled on start.</div>
    </section>

    <section class="quiz-help-section" id="running-classic-quiz">
        <h2>Running a quiz: what do we see when a quiz runs?</h2>
        <p><strong>Use case:</strong> You want to know what students experience during a normal self-paced quiz and what the teacher can monitor while it is in progress.</p>
        <ul>
            <li>Students start from the normal quiz start page with the quiz code.</li>
            <li>Each student gets an individual submission.</li>
            <li>Each answer is logged as the student moves through the quiz.</li>
            <li>The teacher can monitor progress and results from the results/progress screens.</li>
            <li>At the end, students can see their result summary if review is enabled.</li>
        </ul>
    </section>

    <section class="quiz-help-section" id="invalidate-question">
        <h2>Invalidate a question in a quiz that ran and recalculate the scores</h2>
        <p><strong>Use case:</strong> A question turned out to be wrong, ambiguous, or based on material that should not have been tested, and you need to correct the stored results afterward.</p>
        <p>Use this when a question should no longer count after students already completed the quiz.</p>
        <p>In the GUI, this is not shown as a button called <strong>Maintenance</strong>. The actual path is:</p>
        <div class="quiz-help-note">
            <strong>Quizzes</strong> → find the quiz row → <strong>More</strong> → <strong>Regrade Scores</strong>
        </div>
        <p>The <strong>Regrade Scores</strong> page is the maintenance area for that quiz. From there you can invalidate a question, review available backups, and restore a previous backup if needed.</p>
        <ol>
            <li>Open <strong>Quizzes</strong>.</li>
            <li>Find the quiz that already ran.</li>
            <li>Click <strong>More</strong>.</li>
            <li>Click <strong>Regrade Scores</strong>.</li>
            <li>Select the quiz and the question that must be removed from scoring.</li>
            <li>Run the recalculation.</li>
        </ol>
        <div class="quiz-help-note">This changes stored results for existing submissions. A backup is available, but you should still check the impact carefully before recalculating.</div>
    </section>

    <section class="quiz-help-section" id="import-export">
        <h2>Import and export quizzes</h2>
        <p><strong>Use case:</strong> You use AI or an LLM to generate quiz content outside this system and then import that generated quiz into Max Quiz.</p>
        <ul>
            <li>Use export when you want an example structure or template for quiz content.</li>
            <li>Use import when you want to bulk load AI-generated or otherwise prepared quiz content.</li>
            <li>After import, always review question links, order, and active status before using the quiz with students.</li>
        </ul>
    </section>

    <section class="quiz-help-section" id="start-live-quiz">
        <h2>Start live quiz</h2>
        <p><strong>Use case:</strong> You want a teacher-paced classroom game where everyone answers the same question at the same time and the leaderboard is shown after each round.</p>
        <ol>
            <li>Open <strong>Live Quiz</strong> from the main navigation.</li>
            <li>Find the quiz using the search field.</li>
            <li>Select the quiz and click <strong>Create Live Session</strong>.</li>
            <li>Open the student join page and the big-screen page from the teacher session view.</li>
        </ol>
    </section>

    <section class="quiz-help-section" id="run-live-quiz">
        <h2>Running a live quiz</h2>
        <p><strong>Use case:</strong> You already started a live session and want to run it smoothly in class, including joining, opening questions, showing scores, and explaining answers.</p>
        <ul>
            <li>The session starts in the lobby.</li>
            <li>Students join with the code.</li>
            <li>The teacher opens each question manually.</li>
            <li>The teacher closes the question to show the leaderboard.</li>
            <li>The projector screen can also show the full explanation view for the correct answer.</li>
            <li>When the session is done, the teacher finishes it.</li>
        </ul>
    </section>

    <section class="quiz-help-section" id="activate-disable-archive">
        <h2>Activate, disable, and archive quizzes</h2>
        <p><strong>Use case:</strong> You want to control which quizzes are currently available to students and keep old quizzes out of the default list without deleting them.</p>
        <ul>
            <li><strong>Activate</strong> makes a normal quiz available through its code.</li>
            <li><strong>Disable All</strong> resets all active classic quizzes.</li>
            <li><strong>Archive</strong> hides older quizzes from the default list without deleting them.</li>
            <li>Use the active / archived / all filters to find the right list quickly.</li>
        </ul>
    </section>

    <section class="quiz-help-section" id="results-progress">
        <h2>Results, progress, and overview screens</h2>
        <p><strong>Use case:</strong> You want to monitor a running quiz, inspect completed submissions, or compare participation across active quizzes.</p>
        <ul>
            <li><strong>Questions</strong> shows the quiz content and links.</li>
            <li><strong>Results</strong> shows quiz-specific submission data.</li>
            <li><strong>Overview</strong> shows grouped progress across active quizzes.</li>
            <li><strong>Live Quiz</strong> is separate and manages teacher-paced sessions.</li>
        </ul>
    </section>

    <section class="quiz-help-section" id="delete-live-session">
        <h2>Delete a finished live session</h2>
        <p><strong>Use case:</strong> You want to clean up old finished live sessions so the live-session overview stays focused on current work.</p>
        <ol>
            <li>Open the Live Quiz overview.</li>
            <li>Find the finished session.</li>
            <li>Click <strong>Delete</strong>.</li>
        </ol>
        <p>This removes the live-session data and linked live answers, but it does not remove the original quiz itself.</p>
    </section>
</div>
