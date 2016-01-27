<?php
/**
 * Date: 03.02.2015
 * Time: 09:25 ч.
 * @author Nikola Kostadinov
 */

namespace nkostadinov\taxonomy\components\terms;

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\db\Exception;
use yii\db\Migration;
use yii\db\Query;
use yii\db\Schema;
use yii\helpers\ArrayHelper;

class PropertyTerm extends BaseTerm {

    public $updateOnExist = true;

    public function install()
    {
        parent::install();

        $migration = new Migration();
        $migration->createTable($this->getTable(), [
            'id' => Schema::TYPE_PK,
            'object_id' => Schema::TYPE_INTEGER,
            'term_id' => Schema::TYPE_BIGINT,
            'value' => Schema::TYPE_STRING,
        ]);
        if ($migration->db->driverName === 'mysql') {
            $migration->addForeignKey('fk_' . $this->getTable() . '_' . $this->getRefTableName(), $this->getTable(), 'object_id', $this->getRefTableName(), 'id', 'CASCADE');
            $migration->addForeignKey('fk_' . $this->getTable() . '_' . TaxonomyTerms::tableName(), $this->getTable(), 'term_id', TaxonomyTerms::tableName(), 'id', 'CASCADE');
        }
    }

    public function addTerm($object_id, $params)
    {
        foreach($params as $item => $value) {
            $term = TaxonomyTerms::findOne(['term' => $item, 'taxonomy_id' => $this->id]);
            if (!isset($term)) {
                $term = new TaxonomyTerms();
                $term->taxonomy_id = $this->id;
                $term->term = $item;
                $term->total_count = 0;
                $term->save();
            }
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            $query = new Query();
            if (!$query->select(1)->from($this->getTable())->where($data)->exists($this->getDb())) {
                $transaction = $this->getDb()->beginTransaction();
                try {
                    $data['value'] = $value;
                    $this->getDb()->createCommand()->insert($this->getTable(), $data)->execute();

                    $term->updateCounters(['total_count' => 1]);
                    TaxonomyDef::updateAllCounters(['total_count' => 1], ['id' => $this->id]);

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            } elseif($this->updateOnExist) {
                $this->getDb()->createCommand()->update($this->getTable(), [ 'value' => $value ], $data)->execute();
            }
        }
    }

    public function removeTerm($object_id, $params = [])
    {
        $terms = $this->getTerms($object_id, isset($params['name']) ? $params['name'] : []);
        foreach($terms as $term=>$value) {
            $term = $this->getTaxonomyTerm($term);
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            $query = new Query();
            if ($query->select(1)->from($this->getTable())->where($data)->exists($this->getDb())) {
                $this->getDb()->createCommand()->delete($this->getTable(), $data)->execute();

                $term->updateCounters(['total_count' => -1]);
                TaxonomyDef::updateAllCounters(['total_count' => -1], [ 'id' => $this->id ]);
            }
        }
    }

    public function getTerms($object_id, $name = [])
    {
        $query = (new Query())
            ->select(TaxonomyTerms::tableName() . '.term, ' . $this->getTable() . '.value')
            ->from(TaxonomyTerms::tableName())
            ->innerJoin($this->getTable(), $this->getTable() . '.term_id = taxonomy_terms.id and ' . $this->getTable() . '.object_id=:object_id',
                [':object_id' => $object_id])
            ->andFilterWhere([TaxonomyTerms::tableName() . '.term' => $name]);
        foreach($query->all() as $v)
            $result[$v['term']] = $v['value'];
        return isset($result) ? $result : [];
    }
}
