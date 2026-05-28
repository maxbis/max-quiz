<?php

use app\live\models\LiveSession;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Live Session #' . $session->id;
$screenUrl = Url::to(['/live/screen/view', 'code' => $session->join_code], true);
$studentUrl = Url::to(['/live/student/index', 'code' => $session->join_code], true);
$qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=520x520&margin=20&data=' . rawurlencode($studentUrl);

$statusConfig = [
    LiveSession::STATUS_LOBBY => ['label' => 'Lobby', 'bg' => '#f59e0b', 'text' => '#fffaf0'],
    LiveSession::STATUS_QUESTION_OPEN => ['label' => 'Question Open', 'bg' => '#0f766e', 'text' => '#ecfeff'],
    LiveSession::STATUS_LEADERBOARD => ['label' => 'Leaderboard', 'bg' => '#2563eb', 'text' => '#eff6ff'],
    LiveSession::STATUS_FINISHED => ['label' => 'Finished', 'bg' => '#475569', 'text' => '#f8fafc'],
];
$status = $statusConfig[$session->status] ?? ['label' => ucfirst((string)$session->status), 'bg' => '#475569', 'text' => '#f8fafc'];
$currentLeader = $presentation['top'][0] ?? null;
?>

<style>
    .live-session-shell {
        --ink: #0f172a;
        --muted: #475569;
        --line: #dbe4ee;
        --panel: #ffffff;
        --accent: #0f766e;
        --accent-soft: #ecfeff;
        --warm: #f8fafc;
    }

    .live-session-shell {
        color: var(--ink);
        padding-bottom: 28px;
    }

    .live-session-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid #d7e2ec;
        border-radius: 28px;
        padding: 32px;
        background:
            radial-gradient(circle at top left, rgba(15, 118, 110, 0.16), transparent 30%),
            radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.12), transparent 28%),
            linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
    }

    .live-session-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(380px, 460px);
        gap: 28px;
        align-items: start;
    }

    .live-session-title {
        margin: 0 0 14px;
        font-size: clamp(2.6rem, 4vw, 4.2rem);
        line-height: 0.96;
        letter-spacing: -0.05em;
        font-weight: 800;
    }

    .live-status-row {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .live-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 0.98rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .live-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.92;
    }

    .live-progress-note {
        color: var(--muted);
        font-size: 1rem;
        font-weight: 600;
    }

    .live-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
        margin-bottom: 22px;
    }

    .live-metric {
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 18px 18px 16px;
        background: rgba(255, 255, 255, 0.72);
    }

    .live-metric-label {
        display: block;
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
    }

    .live-metric-value {
        display: block;
        font-size: 1.9rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .live-metric-sub {
        display: block;
        margin-top: 8px;
        color: var(--muted);
        font-size: 0.95rem;
    }

    .live-link-list {
        display: grid;
        gap: 12px;
        margin-top: 12px;
    }

    .live-link-card {
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 14px 16px;
        background: rgba(255, 255, 255, 0.82);
    }

    .live-link-label {
        display: block;
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
    }

    .live-link-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .live-link-actions a,
    .live-link-actions button {
        border: 0;
        border-radius: 999px;
        padding: 10px 16px;
        font-size: 0.95rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .live-link-open {
        background: #0f172a;
        color: #ffffff;
    }

    .live-link-copy {
        background: #e2e8f0;
        color: #0f172a;
    }

    .live-control-panel,
    .live-section {
        border: 1px solid #d7e2ec;
        border-radius: 28px;
        background: var(--panel);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.05);
    }

    .live-control-panel {
        padding: 24px;
    }

    .live-control-panel h3,
    .live-section h2 {
        margin-top: 0;
        margin-bottom: 16px;
        font-size: 1.9rem;
        letter-spacing: -0.04em;
        font-weight: 800;
    }

    .live-control-group {
        display: grid;
        gap: 12px;
        margin-bottom: 20px;
    }

    .live-control-button {
        width: 100%;
        border: 0;
        border-radius: 18px;
        padding: 16px 18px;
        font-size: 1.06rem;
        font-weight: 800;
        cursor: pointer;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .live-control-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
    }

    .live-control-button.primary {
        background: linear-gradient(135deg, #0f766e 0%, #0f9a76 100%);
        color: #f0fdfa;
    }

    .live-control-button.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #fb7185 100%);
        color: #fffaf0;
    }

    .live-control-button.ghost {
        background: #ffffff;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }

    .live-qr-panel {
        border: 1px solid var(--line);
        border-radius: 24px;
        padding: 18px;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .live-qr-box {
        border-radius: 22px;
        background: #ffffff;
        padding: 18px;
        display: flex;
        justify-content: center;
        box-shadow: inset 0 0 0 1px #eef4fa;
    }

    .live-qr-box img {
        display: block;
        width: 100%;
        max-width: 380px;
        aspect-ratio: 1 / 1;
        border-radius: 14px;
    }

    .live-qr-help {
        margin-top: 14px;
        color: var(--muted);
        font-size: 0.96rem;
        line-height: 1.45;
    }

    .live-qr-url {
        margin-top: 10px;
        color: #1e293b;
        font-size: 0.94rem;
        word-break: break-word;
    }

    .live-section-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }

    .live-section {
        padding: 26px;
    }

    .live-question-box {
        white-space: pre-wrap;
        font-family: Consolas, Menlo, monospace;
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
        border-radius: 18px;
        padding: 20px;
        min-height: 120px;
        font-size: 1.28rem;
        line-height: 1.45;
    }

    .live-answer-progress {
        margin-top: 14px;
        color: var(--muted);
        font-size: 1rem;
        font-weight: 700;
    }

    .live-table {
        width: 100%;
        border-collapse: collapse;
    }

    .live-table th,
    .live-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #e6edf5;
        text-align: left;
    }

    .live-table th {
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .live-table td {
        font-size: 1rem;
    }

    .live-rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #e0f2fe;
        color: #0f172a;
        font-weight: 800;
    }

    .live-move-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 8px 12px;
        background: #dcfce7;
        color: #166534;
        font-weight: 800;
        min-width: 60px;
    }

    @media (max-width: 1200px) {
        .live-session-hero-grid,
        .live-section-grid {
            grid-template-columns: 1fr;
        }

        .live-metrics {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .live-session-hero {
            padding: 22px;
        }

        .live-section,
        .live-control-panel {
            padding: 20px;
        }

        .live-session-title {
            font-size: 2.2rem;
        }
    }
</style>

<div class="live-session-shell">
    <section class="live-session-hero">
        <div class="live-session-hero-grid">
            <div>
                <h1 class="live-session-title"><?= Html::encode($session->quiz ? $session->quiz->name : 'Live Session') ?></h1>

                <div class="live-status-row">
                    <span class="live-status-pill" style="background: <?= Html::encode($status['bg']) ?>; color: <?= Html::encode($status['text']) ?>;">
                        <span class="live-status-dot"></span>
                        <?= Html::encode($status['label']) ?>
                    </span>
                    <span class="live-progress-note">Question <?= (int)$session->current_question_index ?> of <?= (int)$session->question_count ?></span>
                </div>

                <div class="live-metrics">
                    <div class="live-metric">
                        <span class="live-metric-label">Join Code</span>
                        <span class="live-metric-value" style="letter-spacing:0.08em;"><?= Html::encode($session->join_code) ?></span>
                        <span class="live-metric-sub">Students enter this code or scan the QR.</span>
                    </div>
                    <div class="live-metric">
                        <span class="live-metric-label">Students Joined</span>
                        <span class="live-metric-value"><?= count($participants) ?></span>
                        <span class="live-metric-sub">Current lobby size.</span>
                    </div>
                    <div class="live-metric">
                        <span class="live-metric-label">Current Leader</span>
                        <span class="live-metric-value"><?= $currentLeader ? (int)$currentLeader['score'] : 0 ?></span>
                        <span class="live-metric-sub"><?= Html::encode($currentLeader['name'] ?? 'No leader yet') ?></span>
                    </div>
                    <div class="live-metric">
                        <span class="live-metric-label">Scoring</span>
                        <span class="live-metric-value" style="font-size:1.2rem;line-height:1.2;letter-spacing:-0.02em;"><?= Html::encode($session->getScoringModeLabel()) ?></span>
                        <span class="live-metric-sub">Selected for this session.</span>
                    </div>
                </div>

                <div class="live-link-list">
                    <div class="live-link-card">
                        <span class="live-link-label">Student Join</span>
                    <div class="live-link-actions">
                        <a class="live-link-open" href="<?= Html::encode($studentUrl) ?>" target="_blank" rel="noopener">Open Join Page</a>
                        <button type="button" class="live-link-copy" data-copy="<?= Html::encode($studentUrl) ?>">Copy Link</button>
                    </div>
                </div>

                <div class="live-link-card">
                    <span class="live-link-label">Presentation Screen</span>
                    <div class="live-link-actions">
                        <a class="live-link-open" href="<?= Html::encode($screenUrl) ?>" target="_blank" rel="noopener">Open Big Screen</a>
                        <button type="button" class="live-link-copy" data-copy="<?= Html::encode($screenUrl) ?>">Copy Link</button>
                    </div>
                </div>
            </div>
            </div>

            <aside class="live-control-panel">
                <h3>Controls</h3>

                <div class="live-control-group">
                    <?php if ($session->status === LiveSession::STATUS_LOBBY || $session->status === LiveSession::STATUS_LEADERBOARD): ?>
                        <?php if ((int)$session->current_question_index < (int)$session->question_count): ?>
                            <form method="post" action="<?= Url::to(['open-next', 'id' => $session->id]) ?>">
                                <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->getCsrfToken()) ?>">
                                <button type="submit" class="live-control-button primary"><?= $session->status === LiveSession::STATUS_LOBBY ? 'Open Question 1' : 'Open Next Question' ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($session->status === LiveSession::STATUS_QUESTION_OPEN): ?>
                        <form method="post" action="<?= Url::to(['close-question', 'id' => $session->id]) ?>">
                            <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->getCsrfToken()) ?>">
                            <button type="submit" class="live-control-button warning">Close Question + Show Leaderboard</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($session->status !== LiveSession::STATUS_FINISHED): ?>
                        <form method="post" action="<?= Url::to(['finish', 'id' => $session->id]) ?>">
                            <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->getCsrfToken()) ?>">
                            <button type="submit" class="live-control-button ghost">Finish Session</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="live-qr-panel">
                    <h3 style="margin-bottom:14px;">Student QR</h3>
                    <div class="live-qr-box">
                        <img
                            src="<?= Html::encode($qrImageUrl) ?>"
                            alt="QR code for the student join page"
                        >
                    </div>
                    <div class="live-qr-help">Show this on the projector before the round starts so students can open the correct join page immediately.</div>
                    <div class="live-qr-url"><?= Html::encode($studentUrl) ?></div>
                </div>
            </aside>
        </div>
    </section>

    <div class="live-section-grid">
        <section class="live-section">
            <h2>Current State</h2>
            <?php if ($currentQuestion): ?>
                <p style="margin-top:0;color:#475569;font-weight:700;">Question <?= (int)$currentQuestion->question_order ?></p>
                <div class="live-question-box"><?= Html::encode($currentQuestion->question->question ?? '') ?></div>
                <div class="live-answer-progress">Answers received: <?= (int)$answerCount ?> / <?= count($participants) ?></div>
            <?php else: ?>
                <p style="margin:0;color:#475569;">No question is active yet.</p>
            <?php endif; ?>
        </section>

        <section class="live-section">
            <h2>Lobby</h2>
            <p style="margin-top:0;color:#475569;font-weight:700;"><?= count($participants) ?> students joined</p>
            <table class="live-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?= Html::encode(trim($participant->submission->first_name . ' ' . $participant->submission->last_name)) ?></td>
                            <td><?= Html::encode($participant->submission->class) ?></td>
                            <td><?= (int)$participant->submission->no_correct ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <div class="live-section-grid">
        <section class="live-section">
            <h2>Top 5</h2>
            <table class="live-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($presentation['top'] as $entry): ?>
                        <tr>
                            <td><span class="live-rank"><?= (int)$entry['rank'] ?></span></td>
                            <td><?= Html::encode($entry['name']) ?></td>
                            <td><?= Html::encode($entry['class']) ?></td>
                            <td><?= (int)$entry['score'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="live-section">
            <h2>Big Movers</h2>
            <?php if ($presentation['movers']): ?>
                <table class="live-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>New Rank</th>
                            <th>Move</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($presentation['movers'] as $entry): ?>
                            <tr>
                                <td><?= Html::encode($entry['name']) ?></td>
                                <td>#<?= (int)$entry['rank'] ?></td>
                                <td><span class="live-move-chip">+<?= (int)$entry['rank_delta'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin:0;color:#475569;">No upward movers yet.</p>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
document.querySelectorAll('[data-copy]').forEach(function (button) {
    button.addEventListener('click', async function () {
        const value = button.getAttribute('data-copy');
        try {
            await navigator.clipboard.writeText(value);
            const original = button.textContent;
            button.textContent = 'Copied';
            setTimeout(function () {
                button.textContent = original;
            }, 1400);
        } catch (error) {
            window.prompt('Copy this link:', value);
        }
    });
});

setTimeout(function () {
    window.location.reload();
}, 5000);
</script>
