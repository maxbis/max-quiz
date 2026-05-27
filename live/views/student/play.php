<?php

use yii\helpers\Html;
use yii\helpers\Url;

$stateUrl = Url::to(['state', 'code' => $session->join_code]);
$submitUrl = Url::to(['submit-answer', 'code' => $session->join_code]);
$csrfToken = Yii::$app->request->getCsrfToken();
$csrfParam = Yii::$app->request->csrfParam;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Quiz</title>
    <style>
        body { margin:0; font-family:Consolas, Menlo, monospace; background:linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%); color:#0f172a; min-height:100vh; }
        .shell { max-width:980px; margin:0 auto; padding:28px 18px 48px; }
        .hero { background:white; border-radius:24px; padding:24px; box-shadow:0 16px 40px rgba(15, 23, 42, 0.12); }
        .meta { display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; color:#475569; }
        .question { margin-top:22px; padding:24px; background:white; border-radius:24px; box-shadow:0 16px 40px rgba(15, 23, 42, 0.12); }
        .question-text { white-space:pre-wrap; font-size:1.3rem; line-height:1.55; margin-bottom:22px; }
        .answers { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; }
        .answer-btn { text-align:left; border:2px solid #bfdbfe; background:#dbeafe; border-radius:18px; padding:18px; font:inherit; font-size:1rem; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease; }
        .answer-btn:hover { transform:translateY(-2px); box-shadow:0 10px 18px rgba(59, 130, 246, 0.18); }
        .status-card { margin-top:22px; background:#0f172a; color:white; border-radius:22px; padding:24px; }
        .leaderboard { margin-top:22px; background:white; border-radius:24px; padding:24px; box-shadow:0 16px 40px rgba(15, 23, 42, 0.12); }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 8px; border-bottom:1px solid #e2e8f0; text-align:left; }
        .pill { display:inline-block; padding:8px 12px; border-radius:999px; background:#f59e0b; color:white; font-weight:700; }
        .muted { color:#64748b; }
    </style>
</head>
<body>
    <div class="shell">
        <div class="hero">
            <h1 style="margin-top:0;">Live Quiz</h1>
            <div class="meta">
                <div><strong>Player:</strong> <?= Html::encode(trim($submission['first_name'] . ' ' . $submission['last_name'])) ?></div>
                <div><strong>Class:</strong> <?= Html::encode($submission['class']) ?></div>
                <div><strong>Code:</strong> <?= Html::encode($session->join_code) ?></div>
            </div>
            <div id="summary" class="status-card">Connecting to the live session…</div>
        </div>

        <div id="question-card" class="question" style="display:none;"></div>
        <div id="leaderboard-card" class="leaderboard" style="display:none;"></div>
    </div>

    <script>
    const stateUrl = <?= json_encode($stateUrl) ?>;
    const submitUrl = <?= json_encode($submitUrl) ?>;
    const csrfParam = <?= json_encode($csrfParam) ?>;
    const csrfToken = <?= json_encode($csrfToken) ?>;

    async function fetchState() {
        const response = await fetch(stateUrl, { credentials: 'same-origin' });
        const data = await response.json();
        if (!data.ok) {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
            return;
        }
        renderState(data);
    }

    function renderState(data) {
        const summary = document.getElementById('summary');
        const questionCard = document.getElementById('question-card');
        const leaderboardCard = document.getElementById('leaderboard-card');
        const session = data.session;
        const player = data.player;

        leaderboardCard.style.display = 'none';
        questionCard.style.display = 'none';

        if (session.status === 'lobby') {
            summary.innerHTML = '<span class="pill">Lobby</span><p>Waiting for the teacher to open question 1.</p>';
            return;
        }

        if (session.status === 'question_open' && data.question) {
            const answered = data.answeredCurrentQuestion;
            summary.innerHTML = '<span class="pill">Question ' + data.question.order + ' / ' + session.questionCount + '</span>'
                + '<p>Your score: <strong>' + player.score + '</strong>' + (player.rank ? ' • Current rank: <strong>#' + player.rank + '</strong>' : '') + '</p>'
                + '<p>' + (answered ? 'Answer received. Waiting for the teacher to close the question.' : 'Submit one answer for the current question.') + '</p>';

            questionCard.style.display = 'block';
            questionCard.innerHTML = '<div class="question-text">' + escapeHtml(data.question.text).replace(/\n/g, '<br>') + '</div>'
                + '<div class="answers">' + data.question.answers.map(answer => {
                    const disabled = answered ? 'disabled' : '';
                    return '<button class="answer-btn" ' + disabled + ' onclick="submitAnswer(' + answer.answer_no + ')">'
                        + '<strong>' + answer.answer_no + '.</strong> ' + escapeHtml(answer.label)
                        + '</button>';
                }).join('') + '</div>';
            return;
        }

        if (session.status === 'leaderboard') {
            summary.innerHTML = '<span class="pill">Leaderboard</span><p>Your score: <strong>' + player.score + '</strong>' + (player.rank ? ' • Rank: <strong>#' + player.rank + '</strong>' : '') + '</p>';
            leaderboardCard.style.display = 'block';
            leaderboardCard.innerHTML = '<h2 style="margin-top:0;">Top 5</h2><table><thead><tr><th>#</th><th>Name</th><th>Score</th></tr></thead><tbody>'
                + data.top.map(entry => '<tr><td>' + entry.rank + '</td><td>' + escapeHtml(entry.name) + '</td><td>' + entry.score + '</td></tr>').join('')
                + '</tbody></table>';
            return;
        }

        if (session.status === 'finished') {
            summary.innerHTML = '<span class="pill">Finished</span><p>The live quiz has ended. Final score: <strong>' + player.score + '</strong>' + (player.rank ? ' • Final rank: <strong>#' + player.rank + '</strong>' : '') + '</p>';
            return;
        }
    }

    async function submitAnswer(answerNo) {
        const formData = new URLSearchParams();
        formData.append(csrfParam, csrfToken);
        formData.append('answer_no', String(answerNo));
        const response = await fetch(submitUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString(),
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!data.ok && data.message) {
            alert(data.message);
        }
        await fetchState();
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    fetchState();
    setInterval(fetchState, 2000);
    </script>
</body>
</html>
