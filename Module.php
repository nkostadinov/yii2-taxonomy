<?php

namespace nkostadinov\taxonomy;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'nkostadinov\taxonomy\controllers';

    public $component = 'taxonomy';

    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
