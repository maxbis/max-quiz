<?php

namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

class QuizAssignmentWidget extends Widget
{
    public $questionId;
    public $questionLinks = [];
    
    public function run()
    {
        $this->registerAssets();
        
        return $this->render('quiz-assignment', [
            'questionId' => $this->questionId,
            'questionLinks' => $this->questionLinks,
        ]);
    }
    
    private function registerAssets()
    {
        $view = $this->getView();
        
        $js = "
        window.QuizAssignment = {
            questionId: " . Json::encode($this->questionId) . ",
            updateUrl: " . Json::encode(Url::to(['/question/update-quiz-assignments'])) . ",
            
            toggleQuiz: function(quizId, isActive) {
                $.ajax({
                    url: this.updateUrl,
                    method: 'POST',
                    data: {
                        questionId: this.questionId,
                        quizId: quizId,
                        active: isActive ? 1 : 0
                    },
                    success: function(response) {
                        if (response.success) {
                            QuizAssignment.updateUI(quizId, isActive);
                        } else {
                            alert('Error updating quiz assignment');
                        }
                    },
                    error: function() {
                        alert('Error updating quiz assignment');
                    }
                });
            },
            
            updateUI: function(quizId, isActive) {
                const badge = $('#quiz-status-' + quizId);
                const checkbox = $('#quiz-toggle-' + quizId);
                
                if (isActive) {
                    badge.removeClass('bg-secondary').addClass('bg-success').text('Active');
                } else {
                    badge.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                }
                
                checkbox.prop('checked', isActive);
                this.updateCount();
            },
            
            updateCount: function() {
                const activeCount = $('.quiz-toggle:checked').length;
                $('#active-quiz-count').text(activeCount);
            },
            
            bulkUpdate: function(action) {
                const visibleCheckboxes = $('.quiz-item:visible .quiz-toggle');
                visibleCheckboxes.each(function() {
                    const checkbox = $(this);
                    const quizId = checkbox.data('quiz-id');
                    const shouldBeActive = (action === 'select');
                    
                    if (checkbox.prop('checked') !== shouldBeActive) {
                        QuizAssignment.toggleQuiz(quizId, shouldBeActive);
                    }
                });
            }
        };
        
        $(document).ready(function() {
            QuizAssignment.updateCount();
        });
        ";
        
        $view->registerJs($js);
    }
}
