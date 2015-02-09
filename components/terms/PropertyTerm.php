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
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Exception;
use yii\db\Migration;
use yii\db\Query;
use yii\db\Schema;

class PropertyTerm extends BaseTerm {

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
        $migration->addForeignKey('fk_' . $this->table . '_' . $this->getRefTableName(), $this->table, 'object_id', $this->getRefTableName(), 'id', 'CASCADE');
        $migration->addForeignKey('fk_' . $this->table . '_' . TaxonomyTerms::tableName(), $this->table, 'term_id', TaxonomyTerms::tableName(), 'id', 'CASCADE');
    }

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
            $transaction = $this->getDb()->beginTransaction();
            try {
                $data['value'] = $params['value'];
                $this->getDb()->createCommand()->insert($this->table, $data)->execute();

                $term->updateCounters(['total_count' => 1]);
                $tax->updateCounters(['total_count' => 1]);

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }
    }

    public function removeTerm($object_id, $params)
    {
        $term = $this->getTaxonomyTerm($params['name']);
        $data['term_id'] = $term->id;
        $data['object_id'] = $object_id;

        $query = new Query();
        if($query->select(1)->from($this->table)->where($data)->exists($this->getDb())) {
            $this->getDb()->createCommand()->delete($this->table, $data)->execute();

            $term->updateCounters([ 'total_count' => -1]);
            $this->getTaxonomy()->updateCounters([ 'total_count' => -1]);
        }
    }

    public function getTerms($object_id, $name = [])
    {
        $query = (new Query())
            ->select(TaxonomyTerms::tableName() . '.term, ' . $this->table . '.value')
            ->from(TaxonomyTerms::tableName())
            ->innerJoin($this->table, $this->table . '.term_id = taxonomy_terms.id and ' . $this->table . '.object_id=:object_id',
                [':object_id' => $object_id])
            ->andFilterWhere(['taxonomy_terms.term' => $name]);
        foreach($query->all() as $v)
            $result[$v['term']] = $v['value'];
        return isset($result) ? $result : [];
    }
}