<?php
/**
 * Created by PhpStorm.
 * User: Phreak
 * Date: 03.02.2015
 * Time: 09:25 ч.
 */

namespace nkostadinov\taxonomy\components\terms;


use nkostadinov\taxonomy\models\Taxonomy;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\db\Migration;
use yii\db\Query;
use yii\db\Schema;

class PropertyTerm extends BaseTerm {

    public function addTerm($object_id, $params)
    {
        $tax = $this->getTaxonomy();
        $term = TaxonomyTerms::findOne(['term'=>$params['name'], 'taxonomy_id' => $tax->id]);
        if(!isset($term))
        {
            $term = new TaxonomyTerms();
            $term->taxonomy_id = $tax->id;
            $term->term = $params['name'];
            $term->total_count = 0;
            $term->save();
        }
        $data['term_id'] = $term->id;
        $data['object_id'] = $object_id;

        $query = new Query();
        if(!$query->select(1)->from($this->table)->where($data)->exists($this->getDb())) {
            $data['value'] = $params['value'];
            $this->getDb()->createCommand()->insert($this->table, $data)->execute();

            $term->updateCounters([ 'total_count' => 1]);
            $tax->updateCounters([ 'total_count' => 1]);
        }
    }

    public function isInstalled()
    {
        return Taxonomy::find()->andFilterWhere(['name' => $this->name])->exists();
    }

    public function install()
    {
        parent::install();

        $migration = new Migration();
        $migration->createTable($this->table, [
            'id' => Schema::TYPE_PK,
            'object_id' => Schema::TYPE_INTEGER,
            'term_id' => Schema::TYPE_BIGINT,
            'value' => Schema::TYPE_STRING,
        ]);
        $migration->addForeignKey('fk_' . $this->table . '_' . $this->getRefTableName(), $this->table, 'object_id', $this->getRefTableName(), 'id');
        $migration->addForeignKey('fk_' . $this->table . '_' . TaxonomyTerms::tableName(), $this->table, 'term_id', TaxonomyTerms::tableName(), 'id');
    }

    public function getRefTableName()
    {
        return call_user_func([$this->refTable, 'tableName']);
    }

    public function removeTerm($object_id, $params)
    {
        $tax = $this->getTaxonomy();
        $term = TaxonomyTerms::findOne(['term'=>$params['name'], 'taxonomy_id' => $tax->id]);
        $data['term_id'] = $term->id;
        $data['object_id'] = $object_id;

        $query = new Query();
        if($query->select(1)->from($this->table)->where($data)->exists($this->getDb())) {
            $this->getDb()->createCommand()->delete($this->table, $data)->execute();

            $term->updateCounters([ 'total_count' => -1]);
            $tax->updateCounters([ 'total_count' => -1]);
        }
    }
}