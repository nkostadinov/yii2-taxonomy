<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 19.10.2014
 * Time: 10:46 ч.
 */

namespace nkostadinov\taxonomy;

use nkostadinov\taxonomy\components\exceptions\TermNotDefinedException;
use nkostadinov\taxonomy\components\terms\BaseTerm;
use nkostadinov\taxonomy\components\terms\PropertyTerm;
use nkostadinov\taxonomy\components\terms\TagTerm;
use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Migration;
use yii\db\Schema;
use yii\log\Logger;

class Taxonomy extends Component
{
    /* @var Connection The db connection component */
    public $db = 'db';
    public $table = 'taxonomy';
    //cache array of initialized terms
    private $_taxonomy = [];
    //
    public $definitions = [];

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

    public function removeTerm($term, $object_id, $params = [])
    {
        $term = $this->getTerm($term);
        return $term->removeTerm($object_id, $params);
    }

    public function getTerms($term, $object_id, $name = null)
    {
        $term = $this->getTerm($term);
        return $term->getTerms($object_id, $name);
    }

    /**
     * @param $termName
     * @return BaseTerm
     * @throws InvalidConfigException
     * @throws TermNotDefinedException
     */
    public function getTerm($termName, $reload = false)
    {
        if(!isset($this->_taxonomy[$termName]) or $reload) {
            $tax = TaxonomyDef::findOne(['name' => $termName]);
            \Yii::getLogger()->log("Initialising term $termName", Logger::LEVEL_INFO, 'nkostadinov.taxonomy.terms');
            $this->_taxonomy[$termName] = \Yii::createObject($tax->attributes);
        }
        return $this->_taxonomy[$termName];
    }

    public function isInstalled()
    {
        return \Yii::$app->db->getTableSchema(TaxonomyDef::tableName(), true) !== null;
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return array_merge(
            [TagTerm::className(), PropertyTerm::className()],
            $this->definitions
        );
    }
} 