<?php
/**
 * CustomDialogAsset
 * 
 * Asset bundle for the reusable custom dialog component.
 * 
 * Usage in a view file:
 * 
 *   // Register the asset bundle
 *   app\assets\CustomDialogAsset::register($this);
 *   
 *   // Include the dialog HTML
 *   <?= $this->render('@app/views/include/_custom-dialog.php') ?>
 *   
 *   // Now you can use the dialog in your JavaScript:
 *   window.showCustomDialog('Title', 'Message', function() {
 *       // Confirm action
 *   });
 * 
 * @author Max Quiz Team
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Custom Dialog asset bundle
 */
class CustomDialogAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/custom-dialog.css',
    ];
    
    public $js = [
        'js/custom-dialog.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}

