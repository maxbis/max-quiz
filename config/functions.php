<?php

/**
 * Debug function
 * d($var);
 */
function _d($var, $caller = null)
{
    if (!isset($caller)) {
        $caller = debug_backtrace(1)[0];
    }
    echo '<code>Line: ' . $caller['line'] . ' of ' . $caller['file'] . '</code>';
    echo "<pre>";
    echo yii\helpers\VarDumper::dump($var, 10, false);

    echo "</pre>";
}

/**
 * Debug function with die() after
 * dd($var);
 */
function _dd($var)
{
    $caller = debug_backtrace(1)[0];
    _d($var, $caller);
    die();
}



function writeLog($msg = "")
{
    $logDirectory = '../log';
    $log  = date("j-m-Y H:i:s") . " "
        . $_SERVER['REMOTE_ADDR'] . " "
        . Yii::$app->controller->id . "Controller "
        . "action" . Yii::$app->controller->action->id . " "
        . $msg;
    if (!is_dir($logDirectory)) {
        mkdir($logDirectory, 0777, true);
    }
    file_put_contents($logDirectory . '/audit_' . date("dmY") . '.log', $log . PHP_EOL, FILE_APPEND);
}
