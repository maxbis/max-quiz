<?php
use yii\helpers\Html;
?>

<div class="quiz-assignment-widget">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Quiz Assignments</h6>
        <span class="badge bg-info">
            <span id="active-quiz-count">0</span> of <?= count($questionLinks) ?> quizzes
        </span>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-8">
            <input type="text" 
                   id="quiz-search" 
                   class="form-control form-control-sm" 
                   placeholder="🔍 Search quizzes..."
                   onkeyup="filterQuizzes()">
        </div>
        <div class="col-md-4">
            <div class="btn-group btn-group-sm w-100" role="group">
                <button type="button" 
                        class="btn btn-outline-success btn-sm" 
                        onclick="QuizAssignment.bulkUpdate('select')"
                        title="Select all visible quizzes">
                    ✓ All
                </button>
                <button type="button" 
                        class="btn btn-outline-danger btn-sm" 
                        onclick="QuizAssignment.bulkUpdate('deselect')"
                        title="Deselect all visible quizzes">
                    ✗ None
                </button>
            </div>
        </div>
    </div>
    
    <div id="quiz-list" class="quiz-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
        <?php foreach ($questionLinks as $link): ?>
            <div class="quiz-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded" 
                 data-quiz-name="<?= strtolower($link['name']) ?>"
                 style="background-color: #f8f9fa;">
                
                <div class="form-check">
                    <input type="checkbox" 
                           class="form-check-input quiz-toggle" 
                           id="quiz-toggle-<?= $link['id'] ?>"
                           data-quiz-id="<?= $link['id'] ?>"
                           <?= $link['active'] ? 'checked' : '' ?>
                           onchange="QuizAssignment.toggleQuiz(<?= $link['id'] ?>, this.checked)">
                    
                    <label class="form-check-label" for="quiz-toggle-<?= $link['id'] ?>">
                        <strong><?= \yii\helpers\Html::encode($link['name']) ?></strong>
                    </label>
                </div>
                
                <span class="badge <?= $link['active'] ? 'bg-success' : 'bg-secondary' ?>" 
                      id="quiz-status-<?= $link['id'] ?>">
                    <?= $link['active'] ? 'Active' : 'Inactive' ?>
                </span>
                
                <!-- Hidden input for form submission -->
                <?= \yii\helpers\Html::hiddenInput('questionLinks[' . $link['id'] . ']', 0) ?>
                <?= \yii\helpers\Html::hiddenInput('questionLinks[' . $link['id'] . ']', $link['active'] ? 1 : 0, [
                    'id' => 'hidden-quiz-' . $link['id']
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.quiz-assignment-widget .quiz-list {
    background-color: #ffffff;
}

.quiz-assignment-widget .quiz-item:hover {
    background-color: #e9ecef !important;
    cursor: pointer;
}

.quiz-assignment-widget .form-check-label {
    cursor: pointer;
    flex-grow: 1;
}

.quiz-assignment-widget .badge {
    font-size: 0.75em;
    min-width: 60px;
}
</style>

<script>
function filterQuizzes() {
    const searchTerm = document.getElementById('quiz-search').value.toLowerCase();
    const quizItems = document.querySelectorAll('.quiz-item');
    
    quizItems.forEach(item => {
        const quizName = item.getAttribute('data-quiz-name');
        if (quizName.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Update hidden inputs when checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('quiz-toggle')) {
        const quizId = e.target.dataset.quizId;
        const hiddenInput = document.getElementById('hidden-quiz-' + quizId);
        if (hiddenInput) {
            hiddenInput.value = e.target.checked ? 1 : 0;
        }
    }
});
</script>
