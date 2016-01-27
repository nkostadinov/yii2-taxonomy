<?php
/**
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:41 ч.
 */

namespace nkostadinov\taxonomy\components\terms;

use Exception;
use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\db\Query;

class TagTerm extends BaseTerm {

    public $templateFile = '@nkostadinov/taxonomy/migrations/template/tag.php' ;

    public function addTerm($object_id, $params)
    {
        foreach($params as $item) {
            $term = $this->getTaxonomyTerm($item);
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            $query = new Query();
            if (!$query->select(1)->from($this->table)->where($data)->exists($this->getDb())) {
                $transaction = $this->getDb()->beginTransaction();
                try {
                    $this->getDb()->createCommand()->insert($this->table, $data)->execute();

                    $term->updateCounters(['total_count' => 1]);
                    TaxonomyDef::updateAllCounters(['total_count' => 1], [ 'id' => $this->id ]);

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }
    }

    public function removeTerm($object_id, $params = [])
    {
        if(empty($params))
            $params = $this->getTerms($object_id);
        foreach($params as $item) {
            $term = $this->getTaxonomyTerm($item);
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            $query = new Query();
            if ($query->select(1)->from($this->table)->where($data)->exists($this->getDb())) {
                $this->getDb()->createCommand()->delete($this->table, $data)->execute();

                $term->updateCounters(['total_count' => -1]);
                Taxonomydef::updateAllCounters(['total_count' => -1], [ 'id' => $this->id ]);
            }
        }
    }

    public function getTerms($object_id, $name = [])
    {
        $query = (new Query())
            ->select(TaxonomyTerms::tableName() . '.term')
            ->from(TaxonomyTerms::tableName())
            ->innerJoin($this->table, $this->table . '.term_id = taxonomy_terms.id and ' . $this->table . '.object_id=:object_id',
                [':object_id' => $object_id])
            ->andFilterWhere(['taxonomy_terms.term' => $name]);
        $result = [];
        foreach($query->all() as $v)
            $result[] = $v['term'];
        return $result;
    }

    public function listTerms()
    {
        $terms = TaxonomyTerms::find()
            ->select('term')
            ->where(['taxonomy_id' => $this->id])
            ->asArray()
            ->all();

        return array_column($terms, 'term');
    }
}
