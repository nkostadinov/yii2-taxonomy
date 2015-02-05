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
use nkostadinov\taxonomy\models\TaxonomyTerms;

abstract class BaseTerm implements ITaxonomyTermInterface
{
    public $name; //the name of the term
    public $table;
    public $is_multi = false;
    public $db = 'db';

    private $_taxonomy;

    public abstract function addTerm($object_id, $params);
    public abstract function removeTerm($object_id, $params);
    public abstract function getTerms($object_id, $name = []);

    public function isInstalled()
    {
        return Taxonomy::find()->andFilterWhere(['name' => $this->name])->exists();
    }

    public function install()
    {
        if($this->canInstall()) {
            $taxonomy = new Taxonomy();
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
    public function getDb()
    {
        return \Yii::$app->{$this->db};
    }

    public function canInstall() {
        if(!$this->table)
            return 'Missing "table" property';
        return true;
    }

    public function getTaxonomy()
    {
        if(!isset($this->_taxonomy))
            $this->_taxonomy = Taxonomy::findOne(['name' => $this->name]);
        return $this->_taxonomy;
    }

    public function getTaxonomyTerm($name, $create = true)
    {
        $term = TaxonomyTerms::findOne(['term'=>$name, 'taxonomy_id' => $this->getTaxonomy()->id]);
        if($create and !isset($term))
        {
            $term = new TaxonomyTerms();
            $term->taxonomy_id = $tax->id;
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
}