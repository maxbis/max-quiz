<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Live Quiz';
$selectedQuizId = isset($selectedQuizId) ? (int)$selectedQuizId : 0;
$selectedQuiz = $selectedQuiz ?? null;
$selectedQuizLabel = null;

if ($selectedQuiz !== null) {
    $selectedQuizLabel = $selectedQuiz->quiz_group . ' / ' . $selectedQuiz->name;
    if (!empty($selectedQuiz->language)) {
        $selectedQuizLabel .= ' [' . strtoupper((string)$selectedQuiz->language) . ']';
    }
}
?>

<div class="live-admin-index">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:24px;flex-wrap:wrap;">
        <div style="flex:1;min-width:320px;">
            <h1>Live Quiz Control</h1>
            <p>Create a teacher-paced session from any existing quiz. Students join the lobby with the generated code.</p>
        </div>
        <div style="flex:0 0 460px;width:min(100%, 460px);min-width:320px;max-width:460px;padding:20px;border:1px solid #d0d7de;border-radius:16px;background:#f8fafc;box-sizing:border-box;">
            <h3 style="margin-top:0;">Create Session</h3>
            <form method="post" action="<?= Url::to(['create']) ?>" style="width:100%;">
                <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->getCsrfToken()) ?>">
                <label for="quiz_filter" style="display:block;margin-bottom:8px;font-weight:600;">Search quiz</label>
                <input
                    id="quiz_filter"
                    type="text"
                    class="form-control"
                    placeholder="Type quiz name, group, or use * as wildcard"
                    style="width:100%;margin-bottom:12px;"
                    autocomplete="off"
                >
                <label for="quiz_id" style="display:block;margin-bottom:8px;font-weight:600;">Quiz</label>
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
                <div style="margin-top:8px;color:#64748b;font-size:0.92rem;">
                    <?= $selectedQuizLabel !== null ? 'Change the quiz here if needed before starting the session.' : 'Select a quiz to start a live session.' ?>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px;">Create Live Session</button>
            </form>
        </div>
    </div>

    <div style="margin-top:32px;">
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
