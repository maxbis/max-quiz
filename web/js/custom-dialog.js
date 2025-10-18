/**
 * Custom Dialog Component JavaScript
 * 
 * Provides a modern, reusable dialog system with support for:
 * - Simple confirmation dialogs
 * - Input dialogs for user text entry
 * - Keyboard shortcuts (Enter to confirm, Escape to cancel)
 * - Click outside to close
 * 
 * Dependencies: jQuery
 * 
 * API:
 *   window.showCustomDialog(title, message, onConfirm, showInput, defaultValue)
 *   window.closeCustomDialog()
 */

(function($) {
    'use strict';

    /**
     * Show the custom dialog
     * @param {string} title - Dialog title
     * @param {string} message - Dialog message (supports HTML)
     * @param {function} onConfirm - Callback function when confirmed
     * @param {boolean} showInput - Show input field (default: false)
     * @param {string} defaultValue - Default value for input field
     */
    window.showCustomDialog = function(title, message, onConfirm, showInput, defaultValue) {
        showInput = showInput || false;
        defaultValue = defaultValue || '';
        
        $('#dialogTitle').text(title);
        $('#dialogMessage').html(message);
        
        if (showInput) {
            $('#dialogInputContainer').show();
            $('#dialogInput').val(defaultValue);
            setTimeout(function() {
                $('#dialogInput').focus();
            }, 100);
        } else {
            $('#dialogInputContainer').hide();
        }
        
        $('#customDialog').show();
        
        // Store the confirm callback
        window.currentDialogCallback = onConfirm;
    };

    /**
     * Close the custom dialog
     */
    window.closeCustomDialog = function() {
        $('#customDialog').hide();
        window.currentDialogCallback = null;
    };

    // Initialize dialog event handlers when document is ready
    $(document).ready(function() {
        
        // Handle dialog close button (X)
        $('#dialogCloseBtn').on('click', function() {
            window.closeCustomDialog();
        });

        // Handle dialog cancel button
        $('#dialogCancelBtn').on('click', function() {
            window.closeCustomDialog();
        });

        // Handle dialog confirm button
        $('#dialogConfirmBtn').on('click', function() {
            if (window.currentDialogCallback) {
                window.currentDialogCallback();
            }
            window.closeCustomDialog();
        });

        // Handle dialog input enter key
        $('#dialogInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $('#dialogConfirmBtn').click();
            }
        });

        // Handle dialog overlay click to close
        $('#customDialog').on('click', function(e) {
            if (e.target === this) {
                window.closeCustomDialog();
            }
        });

        // Handle escape key to close dialog
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#customDialog').is(':visible')) {
                window.closeCustomDialog();
            }
        });
    });

})(jQuery);

