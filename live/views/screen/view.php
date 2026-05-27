<?php

use yii\helpers\Url;

$stateUrl = Url::to(['state', 'code' => $session->join_code]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Quiz Screen</title>
    <style>
        body { margin:0; font-family:Consolas, Menlo, monospace; color:#f8fafc; background:
            radial-gradient(circle at top left, rgba(245, 158, 11, 0.45), transparent 28%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.35), transparent 24%),
            linear-gradient(160deg, #0f172a 0%, #111827 100%);
            min-height:100vh; }
        .shell { max-width:1400px; margin:0 auto; padding:32px; }
        .topbar { display:flex; justify-content:space-between; gap:20px; align-items:flex-start; }
        .badge { display:inline-block; background:#f59e0b; color:white; border-radius:999px; padding:10px 14px; font-weight:700; letter-spacing:0.05em; }
        .card { background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); backdrop-filter:blur(12px); border-radius:26px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
        .join-panel { min-width:320px; max-width:360px; }
        .question { margin-top:28px; }
        .question-text { font-size:2rem; line-height:1.45; white-space:pre-wrap; margin-bottom:24px; }
        .answers { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; }
        .answer { padding:20px; border-radius:18px; background:rgba(255,255,255,0.09); border:1px solid rgba(255,255,255,0.12); font-size:1.2rem; }
        .leader-grid { display:grid; grid-template-columns:1.35fr 0.9fr; gap:24px; margin-top:28px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:14px 10px; border-bottom:1px solid rgba(255,255,255,0.12); text-align:left; font-size:1.1rem; }
        .correct { margin-top:20px; padding:18px 72px 18px 20px; border-radius:22px; background:linear-gradient(135deg, rgba(16, 185, 129, 0.24) 0%, rgba(5, 150, 105, 0.18) 100%); border:1px solid rgba(16, 185, 129, 0.6); box-shadow:0 0 0 2px rgba(16, 185, 129, 0.12) inset; font-size:1.2rem; line-height:1.4; position:relative; }
        .correct-toggle { cursor:pointer; transition:transform .15s ease, box-shadow .15s ease, background .15s ease; }
        .correct-toggle:hover { transform:translateY(-1px); box-shadow:0 14px 24px rgba(16, 185, 129, 0.16), 0 0 0 2px rgba(16, 185, 129, 0.12) inset; }
        .correct::after { content:'✓'; position:absolute; top:50%; right:20px; transform:translateY(-50%); width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#10b981; color:#ecfdf5; font-size:1.2rem; font-weight:900; box-shadow:0 10px 20px rgba(16, 185, 129, 0.25); }
        .muted { color:#cbd5e1; }
        .lobby { margin-top:28px; font-size:2rem; }
        .explain-overlay { position:fixed; inset:0; background:rgba(2, 6, 23, 0.78); backdrop-filter:blur(10px); display:none; align-items:center; justify-content:center; padding:32px; z-index:1000; }
        .explain-overlay.open { display:flex; }
        .explain-dialog { width:min(1100px, 100%); max-height:calc(100vh - 64px); overflow:auto; border-radius:30px; padding:30px; background:linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(15, 23, 42, 0.94) 100%); border:1px solid rgba(255,255,255,0.12); box-shadow:0 26px 80px rgba(0,0,0,0.35); }
        .explain-topbar { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:28px; }
        .explain-heading { display:flex; flex-direction:column; align-items:flex-start; gap:14px; }
        .explain-title { margin:0; font-size:2.1rem; line-height:1.05; }
        .explain-close { border:0; border-radius:999px; padding:12px 18px; background:rgba(255,255,255,0.1); color:#f8fafc; font:inherit; font-weight:700; cursor:pointer; }
        .explain-question { white-space:pre-wrap; font-size:1.8rem; line-height:1.45; margin-bottom:20px; color:#f8fafc; }
        .explain-answers { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; }
        .explain-answer { position:relative; padding:18px 72px 18px 20px; border-radius:22px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); font-size:1.2rem; line-height:1.4; }
        .explain-answer.correct-answer { background:linear-gradient(135deg, rgba(16, 185, 129, 0.24) 0%, rgba(5, 150, 105, 0.18) 100%); border-color:rgba(16, 185, 129, 0.6); box-shadow:0 0 0 2px rgba(16, 185, 129, 0.12) inset; }
        .explain-answer.correct-answer::after { content:'✓'; position:absolute; top:50%; right:20px; transform:translateY(-50%); width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#10b981; color:#ecfdf5; font-size:1.2rem; font-weight:900; box-shadow:0 10px 20px rgba(16, 185, 129, 0.25); }
        @media (max-width: 1100px) {
            .topbar { flex-direction:column; }
            .join-panel { width:100%; max-width:none; }
            .explain-answers { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <div>
                <div class="badge" id="status-badge">Connecting…</div>
                <h1 id="quiz-name" style="margin:18px 0 8px;font-size:3rem;">Live Quiz</h1>
                <div class="muted" id="session-meta">Waiting for session state…</div>
            </div>
            <div class="card join-panel">
                <div style="font-size:1rem;color:#cbd5e1;">Student join</div>
                <div id="join-code" style="font-size:3rem;letter-spacing:0.18em;font-weight:700;"></div>
                <div id="player-count" class="muted" style="margin-top:10px;"></div>
            </div>
        </div>

        <div id="content"></div>
    </div>

    <div id="explain-overlay" class="explain-overlay" aria-hidden="true">
            <div class="explain-dialog">
            <div class="explain-topbar">
                <div class="explain-heading">
                    <div class="badge">EXPLANATION</div>
                    <h2 class="explain-title" id="explain-title">Question</h2>
                </div>
                <button type="button" class="explain-close" id="explain-close">Close</button>
            </div>
            <div class="explain-question" id="explain-question"></div>
            <div class="explain-answers" id="explain-answers"></div>
        </div>
    </div>

    <script>
    const stateUrl = <?= json_encode($stateUrl) ?>;
    let latestQuestion = null;

    async function fetchState() {
        const response = await fetch(stateUrl);
        const data = await response.json();
        if (!data.ok) {
            return;
        }
        render(data);
    }

    function render(data) {
        latestQuestion = data.question || null;
        document.getElementById('status-badge').textContent = data.session.status.replace('_', ' ').toUpperCase();
        document.getElementById('quiz-name').textContent = data.session.quizName;
        document.getElementById('join-code').textContent = data.session.joinCode;
        document.getElementById('player-count').textContent = data.totalPlayers + ' students joined';
        document.getElementById('session-meta').textContent = 'Question ' + data.session.currentQuestionIndex + ' of ' + data.session.questionCount;

        const content = document.getElementById('content');

        if (data.session.status === 'lobby') {
            content.innerHTML = '<div class="card lobby"><strong>Lobby open.</strong><br><span class="muted">Students can join now. The first question will appear here when the teacher starts.</span></div>';
            return;
        }

        if (data.session.status === 'question_open' && data.question) {
            content.innerHTML = '<div class="card question">'
                + '<div class="question-text">' + escapeHtml(data.question.text).replace(/\n/g, '<br>') + '</div>'
                + '<div class="answers">' + data.question.answers.map(answer => '<div class="answer"><strong>' + answer.answer_no + '.</strong> ' + escapeHtml(answer.label) + '</div>').join('') + '</div>'
                + '<div class="correct">Answers received: <strong>' + data.answerCount + '</strong></div>'
                + '</div>';
            return;
        }

        if (data.session.status === 'leaderboard') {
            content.innerHTML = '<div class="leader-grid">'
                + '<div class="card"><h2 style="margin-top:0;font-size:2rem;">Top 5</h2><table><thead><tr><th>#</th><th>Name</th><th>Class</th><th>Score</th></tr></thead><tbody>'
                + data.top.map(entry => '<tr><td>' + entry.rank + '</td><td>' + escapeHtml(entry.name) + '</td><td>' + escapeHtml(entry.class) + '</td><td>' + entry.score + '</td></tr>').join('')
                + '</tbody></table>'
                + (data.correctAnswer ? '<div class="correct correct-toggle" id="correct-toggle" role="button" tabindex="0"><strong>' + data.correctAnswer.answer_no + '.</strong> ' + escapeHtml(data.correctAnswer.label) + '</div>' : '')
                + '</div>'
                + '<div class="card"><h2 style="margin-top:0;font-size:2rem;">Big Movers</h2>'
                + (data.movers.length ? '<table><thead><tr><th>Name</th><th>Rank</th><th>Move</th></tr></thead><tbody>'
                    + data.movers.map(entry => '<tr><td>' + escapeHtml(entry.name) + '</td><td>#' + entry.rank + '</td><td>+' + entry.rank_delta + '</td></tr>').join('')
                    + '</tbody></table>'
                    : '<p class="muted">No upward movers yet.</p>')
                + '</div></div>';
            bindCorrectToggle();
            return;
        }

        if (data.session.status === 'finished') {
            content.innerHTML = '<div class="card lobby"><strong>Session finished.</strong><br><span class="muted">The teacher has ended the live quiz.</span></div>';
        }
    }

    function bindCorrectToggle() {
        const toggle = document.getElementById('correct-toggle');
        if (!toggle) {
            return;
        }
        toggle.addEventListener('click', openExplanation);
        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openExplanation();
            }
        });
    }

    function openExplanation() {
        if (!latestQuestion) {
            return;
        }

        document.getElementById('explain-title').textContent = 'Question ' + latestQuestion.order;
        document.getElementById('explain-question').innerHTML = escapeHtml(latestQuestion.text).replace(/\n/g, '<br>');
        document.getElementById('explain-answers').innerHTML = latestQuestion.answers.map(answer => {
            const isCorrect = Number(answer.answer_no) === Number(latestQuestion.correctAnswerNo);
            return '<div class=\"explain-answer' + (isCorrect ? ' correct-answer' : '') + '\">'
                + '<strong>' + answer.answer_no + '.</strong> ' + escapeHtml(answer.label)
                + '</div>';
        }).join('');

        const overlay = document.getElementById('explain-overlay');
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
    }

    function closeExplanation() {
        const overlay = document.getElementById('explain-overlay');
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.getElementById('explain-close').addEventListener('click', closeExplanation);
    document.getElementById('explain-overlay').addEventListener('click', function (event) {
        if (event.target === event.currentTarget) {
            closeExplanation();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeExplanation();
        }
    });

    fetchState();
    setInterval(fetchState, 2000);
    </script>
</body>
</html>
