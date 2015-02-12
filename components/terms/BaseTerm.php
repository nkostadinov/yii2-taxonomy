<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:35 ч.
 */

namespace nkostadinov\taxonomy\components\terms;

use nkostadinov\taxonomy\components\interfaces\ITaxonomyTermInterface;
use nkostadinov\taxonomy\models\Taxonomy;
use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\base\Object;

abstract class BaseTerm extends Object implements ITaxonomyTermInterface
{
    public $id;
    public $name; //the name of the term
    public $data_table;
    public $ref_table;
    public $is_multi = false;
    public $created_at;
    public $total_count;

    public abstract function addTerm($object_id, $params);
    public abstract function removeTerm($object_id, $params = []);
    public abstract function getTerms($object_id, $name = []);

    public function isInstalled()
    {
        return \Yii::$app->db->getTableSchema($this->getTable(), true) !== null;
    }

    public function install()
    {
        if($this->canInstall()) {
            $taxonomy = new TaxonomyDef();
            $taxonomy->name = $this->name;
            $taxonomy->class = get_class($this);
            $taxonomy->save();
        }
    }

    /**
     * Return the db connection component.
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return \Yii::$app->db;
    }

    public function canInstall() {
        if(!$this->getTable())
            return 'Missing "table" property';
        return true;
    }

    public function getTaxonomyTerm($name, $create = true)
    {
        $term = TaxonomyTerms::findOne(['term'=>$name, 'taxonomy_id' => $this->id]);
        if($create and !isset($term))
        {
            $term = new TaxonomyTerms();
            $term->taxonomy_id = $this->id;
            $term->term = $name;
            $term->total_count = 0;
            $term->save();
        }
        return $term;
    }

    public function getRefTableName()
    {
        return call_user_func([$this->refTable, 'tableName']);
    }

    public function getTable()
    {
        return $this->data_table;
    }

    public function getRefTable()
    {
        return $this->ref_table;
    }
}