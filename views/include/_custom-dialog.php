<?php
/**
 * Custom Dialog Modal
 * 
 * A reusable modern dialog component that can be included on any page.
 * 
 * Usage:
 *   <?= $this->render('@app/views/include/_custom-dialog.php') ?>
 * 
 * Or with CustomDialogAsset:
 *   app\assets\CustomDialogAsset::register($this);
 *   <?= $this->render('@app/views/include/_custom-dialog.php') ?>
 * 
 * JavaScript API:
 *   window.showCustomDialog(title, message, onConfirm, showInput, defaultValue)
 *   window.closeCustomDialog()
 */
?>

<!-- Modern Dialog Modal -->
<div id="customDialog" class="custom-dialog-overlay" style="display: none;">
    <div class="custom-dialog">
        <div class="custom-dialog-header">
            <h4 id="dialogTitle">Confirm Action</h4>
            <button type="button" class="custom-dialog-close" id="dialogCloseBtn">&times;</button>
        </div>
        <div class="custom-dialog-body">
            <p id="dialogMessage">Are you sure you want to proceed?</p>
            <div id="dialogInputContainer" style="display: none;">
                <label for="dialogInput">Filename (without extension):</label>
                <input type="text" id="dialogInput" class="form-control" placeholder="Enter filename...">
            </div>
        </div>
        <div class="custom-dialog-footer">
            <button type="button" class="btn btn-secondary" id="dialogCancelBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="dialogConfirmBtn">Confirm</button>
        </div>
    </div>
</div>

