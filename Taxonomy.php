<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 10:46 ч.
 */

namespace nkostadinov;


use nkostadinov\taxonomy\exceptions\TermNoDefinedException;
use yii\base\Object;

class Taxonomy extends Object {

    public $config = [];
    /* The db connection component */
    public $db = 'db';

    public function addTerm($term, $value)
    {
        $term = $this->loadTerm($term);
    }

    public function loadTerm($term)
    {
        if(!isset($this->config[$term]))
            throw new TermNoDefinedException("The term \"$term\" is not defined in config.");

        return \Yii::createObject($this->config[$term]);
    }
} 