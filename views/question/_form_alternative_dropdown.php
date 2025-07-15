<?php
/* Alternative 3: Simple Dropdown Multi-Select Implementation
 * 
 * Replace the existing quiz assignment section in _form.php with this code:
 * This creates a searchable dropdown that shows full quiz names
 */
?>

<hr>

<?php if (isset($questionLinks) && $questionLinks != '' ) { ?>
    <div class="row justify-content-start">
        <div class="col-12">
            <h6>Quiz Assignments:</h6>
            
            <!-- Dropdown Toggle Button -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="quizDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    📚 Select Quizzes (<span id="selectedCount"><?= count(array_filter($questionLinks, function($link) { return $link['active']; })) ?></span>)
                </button>
                
                <div class="dropdown-menu p-3" style="min-width: 400px; max-height: 400px; overflow-y: auto;" aria-labelledby="quizDropdown">
                    <!-- Search Box -->
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm" id="quizDropdownSearch" placeholder="🔍 Search quizzes..." onkeyup="filterDropdownQuizzes()">
                    </div>
                    
                    <!-- Select All/None buttons -->
                    <div class="d-flex justify-content-between mb-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="selectAllDropdownQuizzes()">Select All</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deselectAllDropdownQuizzes()">Deselect All</button>
                    </div>
                    
                    <hr class="my-2">
                    
                    <!-- Quiz List -->
                    <div id="dropdownQuizList">
                        <?php foreach ($questionLinks as $link): ?>
                            <div class="dropdown-quiz-item form-check mb-2" data-quiz-name="<?= strtolower($link['name']) ?>">
                                <?= Html::hiddenInput('questionLinks[' . $link['id'] . ']', 0) ?>
                                <input type="checkbox" 
                                       class="form-check-input dropdown-quiz-checkbox" 
                                       id="dropdown_quiz_<?= $link['id'] ?>"
                                       name="questionLinks[<?= $link['id'] ?>]"
                                       value="1"
                                       <?= $link['active'] ? 'checked' : '' ?>
                                       onchange="updateDropdownCount()">
                                <label class="form-check-label" for="dropdown_quiz_<?= $link['id'] ?>">
                                    <?= Html::encode($link['name']) ?>
                                    <small class="text-muted">(ID: <?= $link['id'] ?>)</small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Selected Quizzes Preview -->
            <div class="mt-2">
                <small class="text-muted">Selected quizzes:</small>
                <div id="selectedQuizzesPreview" class="mt-1"></div>
            </div>
        </div>
    </div>

    <script>
        function filterDropdownQuizzes() {
            const searchTerm = document.getElementById('quizDropdownSearch').value.toLowerCase();
            const quizItems = document.querySelectorAll('.dropdown-quiz-item');
            
            quizItems.forEach(item => {
                const quizName = item.getAttribute('data-quiz-name');
                if (quizName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function selectAllDropdownQuizzes() {
            const visibleCheckboxes = document.querySelectorAll('.dropdown-quiz-item:not([style*="display: none"]) .dropdown-quiz-checkbox');
            visibleCheckboxes.forEach(checkbox => checkbox.checked = true);
            updateDropdownCount();
        }

        function deselectAllDropdownQuizzes() {
            const visibleCheckboxes = document.querySelectorAll('.dropdown-quiz-item:not([style*="display: none"]) .dropdown-quiz-checkbox');
            visibleCheckboxes.forEach(checkbox => checkbox.checked = false);
            updateDropdownCount();
        }

        function updateDropdownCount() {
            const checkedBoxes = document.querySelectorAll('.dropdown-quiz-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkedBoxes.length;
            
            // Update preview
            const selectedNames = Array.from(checkedBoxes).map(checkbox => {
                const label = document.querySelector(`label[for="${checkbox.id}"]`);
                return label.textContent.split('(ID:')[0].trim();
            });
            
            const preview = document.getElementById('selectedQuizzesPreview');
            if (selectedNames.length > 0) {
                const displayNames = selectedNames.slice(0, 3).map(name => 
                    `<span class="badge bg-info me-1">${name}</span>`
                ).join('');
                const moreCount = selectedNames.length > 3 ? ` <small>+${selectedNames.length - 3} more</small>` : '';
                preview.innerHTML = displayNames + moreCount;
            } else {
                preview.innerHTML = '<span class="text-muted">None selected</span>';
            }
        }

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', updateDropdownCount);
    </script>
<?php } ?>
