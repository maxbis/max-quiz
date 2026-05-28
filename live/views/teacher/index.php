<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Live Quiz';
$selectedQuizId = isset($selectedQuizId) ? (int)$selectedQuizId : 0;
$selectedQuiz = $selectedQuiz ?? null;
$selectedQuizLabel = null;
$scoringModes = $scoringModes ?? [];

if ($selectedQuiz !== null) {
    $selectedQuizLabel = $selectedQuiz->quiz_group . ' / ' . $selectedQuiz->name;
    if (!empty($selectedQuiz->language)) {
        $selectedQuizLabel .= ' [' . strtoupper((string)$selectedQuiz->language) . ']';
    }
}
?>

<style>
    .live-admin-index {
        color: #0f172a;
    }

    .live-admin-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .live-admin-header h1 {
        margin: 0 0 6px;
        font-size: clamp(2.4rem, 4vw, 3.5rem);
        line-height: 0.98;
        letter-spacing: -0.05em;
        font-weight: 800;
    }

    .live-admin-header p {
        margin: 0;
        color: #475569;
        font-size: 1rem;
        max-width: 820px;
    }

    .live-admin-toolbar {
        border: 1px solid #d7e2ec;
        border-radius: 24px;
        padding: 18px 20px;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.05);
        margin-bottom: 22px;
    }

    .live-admin-toolbar-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .live-admin-toolbar-title h2 {
        margin: 0;
        font-size: 1.65rem;
        letter-spacing: -0.04em;
        font-weight: 800;
    }

    .live-admin-toolbar-note {
        color: #64748b;
        font-size: 0.92rem;
        font-weight: 600;
    }

    .live-admin-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.1fr) minmax(260px, 1.2fr) minmax(220px, 0.95fr) auto;
        gap: 14px;
        align-items: start;
    }

    .live-admin-field {
        display: flex;
        flex-direction: column;
        align-self: start;
    }

    .live-admin-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 0.94rem;
        font-weight: 700;
        color: #0f172a;
    }

    .live-admin-help {
        margin-top: 6px;
        color: #64748b;
        font-size: 0.86rem;
        line-height: 1.4;
    }

    .live-admin-submit {
        min-width: 210px;
        height: 48px;
        border: 0;
        border-radius: 14px;
        font-size: 1rem;
        font-weight: 800;
        box-shadow: 0 14px 26px rgba(37, 99, 235, 0.16);
    }

    .live-admin-submit-field {
        align-self: end;
        justify-content: flex-end;
    }

    .live-admin-sessions {
        margin-top: 8px;
    }

    .live-admin-sessions h2 {
        margin: 0 0 12px;
        font-size: 2rem;
        letter-spacing: -0.04em;
        font-weight: 800;
    }

    @media (max-width: 1200px) {
        .live-admin-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .live-admin-submit {
            width: 100%;
        }
    }

    @media (max-width: 760px) {
        .live-admin-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="live-admin-index">
    <div class="live-admin-header">
        <div>
            <h1>Live Quiz Control</h1>
            <p>Start a teacher-paced session quickly, then manage active and recent live quizzes below.</p>
        </div>
    </div>

    <section class="live-admin-toolbar">
        <div class="live-admin-toolbar-title">
            <h2>Create Session</h2>
            <span class="live-admin-toolbar-note">Students join the lobby with the generated code after you start the session.</span>
        </div>
        <form method="post" action="<?= Url::to(['create']) ?>" class="live-admin-form">
            <div class="live-admin-field">
                <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->getCsrfToken()) ?>">
                <label for="quiz_filter">Search quiz</label>
                <input
                    id="quiz_filter"
                    type="text"
                    class="form-control"
                    placeholder="Type quiz name, group, or use * as wildcard"
                    autocomplete="off"
                >
            </div>
            <div class="live-admin-field">
                <label for="quiz_id">Quiz</label>
                <select id="quiz_id" name="quiz_id" class="form-select" style="width:100%;" required>
                    <option value="">Select a quiz</option>
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php
                        $label = $quiz->quiz_group . ' / ' . $quiz->name;
                        if (!empty($quiz->language)) {
                            $label .= ' [' . strtoupper((string)$quiz->language) . ']';
                        }
                        $searchText = mb_strtolower(trim($quiz->quiz_group . ' ' . $quiz->name . ' ' . (string)$quiz->language));
                        ?>
                        <option
                            value="<?= (int)$quiz->id ?>"
                            data-search="<?= Html::encode($searchText) ?>"
                            <?= (int)$quiz->id === $selectedQuizId ? 'selected' : '' ?>
                        ><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="live-admin-help">
                    <?= $selectedQuizLabel !== null ? 'Change the quiz here if needed before starting the session.' : 'Select a quiz to start a live session.' ?>
                </div>
            </div>
            <div class="live-admin-field">
                <label for="scoring_mode">Scoring</label>
                    <select id="scoring_mode" name="scoring_mode" class="form-select" style="width:100%;">
                    <?php foreach ($scoringModes as $value => $label): ?>
                        <option value="<?= Html::encode($value) ?>" <?= $value === \app\live\models\LiveSession::SCORING_MODE_CORRECT_DIFFICULTY_BONUS ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="live-admin-help">
                    <strong>Correct + difficulty bonus</strong> is the default. It adds bonus points when fewer students answer a question correctly. <strong>Correct only</strong> gives points for correct answers only.
                </div>
            </div>
            <div class="live-admin-field live-admin-submit-field">
                <button type="submit" class="btn btn-primary live-admin-submit">Create Live Session</button>
            </div>
        </form>
    </section>

    <div class="live-admin-sessions">
        <h2>Recent Sessions</h2>
        <?= GridView::widget([
            'dataProvider' => $sessions,
            'summary' => '',
            'columns' => [
                'id',
                [
                    'label' => 'Quiz',
                    'value' => static fn($model) => $model->quiz ? $model->quiz->name : 'Unknown quiz',
                ],
                'join_code',
                'status',
                'current_question_index',
                'question_count',
                'created_at',
                [
                    'label' => 'Open',
                    'format' => 'raw',
                    'value' => static function ($model) {
                        $actions = [];
                        $actions[] = Html::a('Manage', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']);

                        if ($model->status === \app\live\models\LiveSession::STATUS_FINISHED) {
                            $actions[] = Html::beginForm(['delete', 'id' => $model->id], 'post', ['style' => 'display:inline-block;margin-left:8px;'])
                                . Html::submitButton('Delete', [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data-confirm' => 'Delete this finished live session and all answers linked to it?',
                                ])
                                . Html::endForm();
                        }

                        return implode('', $actions);
                    },
                ],
            ],
        ]) ?>
    </div>
</div>

<script>
(function () {
    const filterInput = document.getElementById('quiz_filter');
    const select = document.getElementById('quiz_id');
    const selectedQuizId = <?= $selectedQuizId ?>;

    if (!filterInput || !select) {
        return;
    }

    const allOptions = Array.from(select.querySelectorAll('option')).map(function (option) {
        return {
            value: option.value,
            text: option.textContent,
            search: option.getAttribute('data-search') || '',
            selected: option.selected,
        };
    });

    function escapeRegex(value) {
        return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function buildMatcher(query) {
        const trimmed = query.trim().toLowerCase();
        if (!trimmed) {
            return null;
        }

        const pattern = '^' + trimmed
            .split('*')
            .map(escapeRegex)
            .join('.*') + '.*$';

        return new RegExp(pattern);
    }

    function renderOptions() {
        const currentValue = select.value || (selectedQuizId ? String(selectedQuizId) : '');
        const matcher = buildMatcher(filterInput.value);
        const matchedOptions = allOptions.filter(function (option, index) {
            if (index === 0) {
                return true;
            }
            if (option.value === currentValue) {
                return true;
            }
            return matcher === null || matcher.test(option.search);
        });

        select.innerHTML = '';
        matchedOptions.forEach(function (option) {
            const element = document.createElement('option');
            element.value = option.value;
            element.textContent = option.text;
            if (option.search) {
                element.setAttribute('data-search', option.search);
            }
            if (option.value === currentValue) {
                element.selected = true;
            }
            select.appendChild(element);
        });

        if (!Array.from(select.options).some(option => option.value === currentValue)) {
            select.value = '';
        }
    }

    filterInput.addEventListener('input', renderOptions);
    renderOptions();

    if (selectedQuizId) {
        select.value = String(selectedQuizId);
    }
})();
</script>
