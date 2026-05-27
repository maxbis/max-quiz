<?php

namespace app\live;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\live\controllers';

    public function init()
    {
        parent::init();
        $this->layout = '@app/views/layouts/main';
    }
}
