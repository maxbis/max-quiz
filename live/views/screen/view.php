<?php

use yii\helpers\Url;

$stateUrl = Url::to(['state', 'code' => $session->join_code]);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();
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
        .action-panel { margin-top:18px; }
        .screen-control-form { margin-top:14px; }
        .screen-control-button { width:100%; min-height:72px; border:0; border-radius:18px; padding:16px 18px; display:flex; align-items:center; justify-content:center; text-align:center; white-space:nowrap; font:inherit; font-size:1.05rem; font-weight:800; cursor:pointer; color:#f0fdfa; background:linear-gradient(135deg, #0f766e 0%, #0f9a76 100%); box-shadow:0 14px 30px rgba(15, 118, 110, 0.28); }
        .screen-control-button.warning { color:#fffaf0; background:linear-gradient(135deg, #f59e0b 0%, #fb7185 100%); box-shadow:0 14px 30px rgba(251, 113, 133, 0.24); }
        .screen-control-button:disabled { opacity:0.7; cursor:wait; }
        .screen-control-hint { margin-top:10px; font-size:0.95rem; line-height:1.45; color:#cbd5e1; }
        .screen-control-error { margin-top:10px; color:#fecaca; font-size:0.92rem; line-height:1.45; }
        .question { margin-top:28px; }
        .question-text { font-size:2rem; line-height:1.45; white-space:pre-wrap; margin-bottom:24px; }
        .answers { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; }
        .answer { padding:20px; border-radius:18px; background:rgba(255,255,255,0.09); border:1px solid rgba(255,255,255,0.12); font-size:1.2rem; }
        .question-metrics { display:flex; gap:16px; flex-wrap:wrap; margin-top:20px; }
        .question-metric { min-width:220px; padding:16px 18px; border-radius:18px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); }
        .question-metric-label { display:block; margin-bottom:8px; color:#cbd5e1; font-size:0.95rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; }
        .question-metric-value { display:block; font-size:1.8rem; line-height:1; font-weight:800; }
        .leader-grid { display:grid; grid-template-columns:1.35fr 0.9fr; gap:24px; margin-top:28px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:14px 10px; border-bottom:1px solid rgba(255,255,255,0.12); text-align:left; font-size:1.1rem; }
        .correct { margin-top:20px; padding:18px 72px 18px 20px; border-radius:22px; background:linear-gradient(135deg, rgba(16, 185, 129, 0.24) 0%, rgba(5, 150, 105, 0.18) 100%); border:1px solid rgba(16, 185, 129, 0.6); box-shadow:0 0 0 2px rgba(16, 185, 129, 0.12) inset; font-size:1.2rem; line-height:1.4; position:relative; }
        .correct-toggle { cursor:pointer; transition:transform .15s ease, box-shadow .15s ease, background .15s ease; }
        .correct-toggle:hover { transform:translateY(-1px); box-shadow:0 14px 24px rgba(16, 185, 129, 0.16), 0 0 0 2px rgba(16, 185, 129, 0.12) inset; }
        .correct::after { content:'✓'; position:absolute; top:50%; right:20px; transform:translateY(-50%); width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#10b981; color:#ecfdf5; font-size:1.2rem; font-weight:900; box-shadow:0 10px 20px rgba(16, 185, 129, 0.25); }
        .correct-stats { margin-top:12px; color:#d1fae5; font-size:1rem; font-weight:700; }
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
                <div id="action-panel" class="action-panel"></div>
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
    const csrfParam = <?= json_encode($csrfParam) ?>;
    const csrfToken = <?= json_encode($csrfToken) ?>;
    let latestQuestion = null;
    let advanceRequestInFlight = false;
    let advanceErrorMessage = '';

    async function fetchState() {
        const response = await fetch(stateUrl);
        const data = await response.json();
        if (!data.ok) {
            return;
        }
        render(data);
    }

    async function submitAdvanceAction(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const button = form.querySelector('button[type="submit"]');
        advanceErrorMessage = '';
        if (button) {
            button.disabled = true;
        }
        advanceRequestInFlight = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams(new FormData(form)).toString(),
                credentials: 'same-origin',
                redirect: 'follow',
            });

            const result = await response.json();
            if (!response.ok || !result.ok) {
                throw new Error(result.message || 'The session could not be advanced.');
            }

            await fetchState();
        } catch (error) {
            advanceErrorMessage = error.message || 'The session could not be advanced.';
        } finally {
            advanceRequestInFlight = false;
            if (button) {
                button.disabled = false;
            }
            if (advanceErrorMessage) {
                renderActionError();
            }
        }
    }

    function render(data) {
        latestQuestion = data.question || null;
        document.getElementById('status-badge').textContent = data.session.status.replace('_', ' ').toUpperCase();
        document.getElementById('quiz-name').textContent = data.session.quizName;
        document.getElementById('join-code').textContent = data.session.joinCode;
        document.getElementById('player-count').textContent = data.totalPlayers + ' students joined';
        document.getElementById('session-meta').textContent = 'Question ' + data.session.currentQuestionIndex + ' of ' + data.session.questionCount;
        renderActionPanel(data);

        const content = document.getElementById('content');

        if (data.session.status === 'lobby') {
            content.innerHTML = '<div class="card lobby"><strong>Lobby open.</strong><br><span class="muted">Students can join now. The first question will appear here when the teacher starts.</span></div>';
            return;
        }

        if (data.session.status === 'question_open' && data.question) {
            content.innerHTML = '<div class="card question">'
                + '<div class="question-text">' + escapeHtml(data.question.text).replace(/\n/g, '<br>') + '</div>'
                + '<div class="answers">' + data.question.answers.map(answer => '<div class="answer"><strong>' + answer.answer_no + '.</strong> ' + escapeHtml(answer.label) + '</div>').join('') + '</div>'
                + '<div class="question-metrics">'
                + '<div class="question-metric"><span class="question-metric-label">Answers received</span><span class="question-metric-value">' + data.answerCount + ' / ' + data.totalPlayers + '</span></div>'
                + '<div class="question-metric"><span class="question-metric-label">Students joined</span><span class="question-metric-value">' + data.totalPlayers + '</span></div>'
                + '</div>'
                + '</div>';
            return;
        }

        if (data.session.status === 'leaderboard') {
            content.innerHTML = '<div class="leader-grid">'
                + '<div class="card"><h2 style="margin-top:0;font-size:2rem;">Top 5</h2><table><thead><tr><th>#</th><th>Name</th><th>Class</th><th>Score</th></tr></thead><tbody>'
                + data.top.map(entry => '<tr><td>' + entry.rank + '</td><td>' + escapeHtml(entry.name) + '</td><td>' + escapeHtml(entry.class) + '</td><td>' + entry.score + '</td></tr>').join('')
                + '</tbody></table>'
                + (data.correctAnswer ? '<div class="correct correct-toggle" id="correct-toggle" role="button" tabindex="0"><strong>' + data.correctAnswer.answer_no + '.</strong> ' + escapeHtml(data.correctAnswer.label) + '</div>'
                    + '<div class="correct-stats">' + data.answerStats.correctPercent + '% correct'
                    + ' <span class="muted">(' + data.answerStats.correct + ' of ' + data.answerStats.submitted + ' answers)</span></div>' : '')
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

    function renderActionPanel(data) {
        const panel = document.getElementById('action-panel');
        if (!panel) {
            return;
        }

        if (!data.canControl || !data.advanceAction) {
            panel.innerHTML = '';
            return;
        }

        const buttonClass = data.advanceAction.type === 'close_question' ? 'screen-control-button warning' : 'screen-control-button';
        panel.innerHTML = '<form method="post" action="' + escapeHtml(data.advanceAction.url) + '" class="screen-control-form">'
            + '<input type="hidden" name="' + escapeHtml(csrfParam) + '" value="' + escapeHtml(csrfToken) + '">'
            + '<button type="submit" class="' + buttonClass + '">' + escapeHtml(data.advanceAction.label) + '</button>'
            + '</form>'
            + '<div class="screen-control-hint">Teacher shortcut for the current next step.</div>';

        const form = panel.querySelector('.screen-control-form');
        if (form) {
            form.addEventListener('submit', submitAdvanceAction);
        }

        if (advanceRequestInFlight) {
            const button = panel.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
            }
        }

        if (advanceErrorMessage) {
            renderActionError();
        }
    }

    function renderActionError() {
        const panel = document.getElementById('action-panel');
        if (!panel || !advanceErrorMessage) {
            return;
        }

        const existingError = panel.querySelector('.screen-control-error');
        if (existingError) {
            existingError.textContent = advanceErrorMessage;
            return;
        }

        const error = document.createElement('div');
        error.className = 'screen-control-error';
        error.textContent = advanceErrorMessage;
        panel.appendChild(error);
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
