<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 10:46 ч.
 */

namespace nkostadinov\taxonomy;


use nkostadinov\taxonomy\components\exceptions\TermNoDefinedException;
use nkostadinov\taxonomy\components\terms\BaseTerm;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\log\Logger;

class Taxonomy extends Component {

    public $config = [];
    /* @var Connection The db connection component */
    public $db = 'db';

    public $table = 'taxonomy';

    public function isTermInstalled($termName)
    {
        $term = $this->getTerm($termName);
        return $term->isInstalled();
    }

    public function addTerm($term, $object_id, $params)
    {
        $term = $this->getTerm($term);
        $term->addTerm($object_id, $params);
    }

    public function removeTerm($term, $object_id, $params)
    {
        $term = $this->getTerm($term);
        $term->removeTerm($object_id, $params);
    }

    /**
     * @param $termName
     * @return BaseTerm
     * @throws InvalidConfigException
     */
    public function getTerm($termName)
    {
        if(!isset($this->config[$termName]))
            throw new TermNoDefinedException("The term \"$termName\" is not defined in config.");
        if(!isset($this->config[$termName]['_instance'])) {
            \Yii::getLogger()->log("Initialising term $termName => " .  $this->config[$termName]['class'], Logger::LEVEL_INFO, 'nkostadinov.taxonomy.terms');
            $this->config[$termName]['_instance'] = \Yii::createObject(array_merge($this->config[$termName], ['name'=>$termName]));
            $this->config[$termName]['_instance']->name = $termName;
        }
        return $this->config[$termName]['_instance'];
    }
} 