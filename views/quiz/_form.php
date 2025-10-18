<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Quiz $model */
/** @var yii\widgets\ActiveForm $form */

$id = Yii::$app->request->get('id');
?>

<style>
    /* Modern Quiz Form Styling - Matching Question Form */
    
    .form-container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .form-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 25px 30px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .form-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
    }
    
    .form-header .quiz-name {
        opacity: 0.9;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    
    .form-body {
        padding: 30px;
    }
    
    .section {
        margin-bottom: 35px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .section-title .icon {
        margin-right: 10px;
        font-size: 1.3rem;
    }
    
    .form-field {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border: 2px solid #e9ecef;
        transition: border-color 0.3s ease;
        margin-bottom: 20px;
    }
    
    .form-field:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        outline: none;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding: 30px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: #28a745;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #007bff;
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        border-color: #ffc107;
        color: #212529;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-color: #dc3545;
    }
    
    .row {
        margin: 0 -10px;
    }
    
    .col-md-6, .col-md-4, .col-md-3 {
        padding: 0 8px;
    }
    
    /* Compact form styling for better space utilization */
    .compact-form .form-group {
        margin-bottom: 20px;
    }
    
    .compact-form .form-group label {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .compact-form .form-control {
        padding: 10px 12px;
        font-size: 0.9rem;
    }
    
    .compact-form .section {
        padding: 20px;
    }
    
    .compact-form .section-title {
        font-size: 1.1rem;
        margin-bottom: 15px;
    }
    
    .character-count {
        font-size: 0.8rem;
        color: #6c757d;
        text-align: right;
        margin-top: 5px;
    }
    
    .character-count.warning {
        color: #ffc107;
    }
    
    .character-count.danger {
        color: #dc3545;
    }
    
    @media (max-width: 768px) {
        .form-container {
            margin: 10px;
            border-radius: 8px;
        }
        
        .form-header {
            padding: 20px;
        }
        
        .form-body {
            padding: 20px;
        }
        
        .section {
            padding: 20px;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .action-buttons .btn {
            width: 100%;
            max-width: 300px;
        }
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h2>📝 Quiz Settings</h2>
        <div class="quiz-name">Editing: <?= Html::encode($model->name) ?></div>
    </div>
    
    <div class="form-body compact-form">
        <?php $form = ActiveForm::begin(); ?>

        <!-- Basic Information Section -->
        <div class="section">
            <div class="section-title">
                <span class="icon">📋</span>
                Basic Information
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('Quiz Name') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'password')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('Unique code to access quiz') ?>
                </div>
            </div>
        </div>

        <!-- Quiz Settings Section - Compact Layout -->
        <div class="section">
            <div class="section-title">
                <span class="icon">⚙️</span>
                Quiz Settings
            </div>
            
            <!-- Compact 3-column layout for settings -->
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'active')->dropDownList(
                        [1 => 'Yes', 0 => 'No'],
                        ['prompt' => 'Select...', 'class' => 'form-control']
                    )->label('Active') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'no_questions')->textInput(['class' => 'form-control', 'type' => 'number', 'min' => 1])->label('Max Questions') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'review')->dropDownList(
                        [1 => 'Review possible', 0 => 'No Review'],
                        ['prompt' => 'Select...', 'class' => 'form-control']
                    )->label('Review Quiz') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'blind')->dropDownList(
                        [0 => 'On Screen', 1 => 'On Paper'],
                        ['prompt' => 'Select...', 'class' => 'form-control']
                    )->label('Blind Quiz') ?>
                </div>
            </div>
            
            <!-- Second row for remaining settings -->
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'ip_check')->dropDownList(
                        [0 => 'Everyone Allowed', 1 => 'IP Restricted'],
                        ['prompt' => 'Select...', 'class' => 'form-control']
                    )->label('IP Check') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'random')->dropDownList(
                        [0 => 'Questions in right order', 1 => 'Random Order'],
                        ['prompt' => 'Select...', 'class' => 'form-control']
                    )->label('Sequential (label,id)') ?>
                </div>
                <div class="col-md-6">
                    <!-- Empty space for future fields or can be removed -->
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?= Html::submitButton('💾 Save', ['class' => 'btn btn-success quiz-button']) ?>
            <?= Html::a('⬅️ Back', Yii::$app->request->referrer, ['class' => 'btn btn-primary quiz-button']); ?>
            <?= Html::a('👁️ Preview', ['/quiz/view', 'id' => $model->id], ['class' => 'btn btn-warning quiz-button', 'target' => '_blank']); ?>
            <?= Html::a('📄 PDF', ['/question/pdf', 'quiz_id' => $model->id], ['class' => 'btn btn-danger quiz-button', 'target' => '_blank']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>