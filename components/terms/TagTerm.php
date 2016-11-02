<?php
/**
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:41 ч.
 */

namespace nkostadinov\taxonomy\components\terms;

use Exception;
use nkostadinov\taxonomy\models\TaxonomyDef;
use yii\db\Query;

class TagTerm extends BaseTerm
{
    public $templateFile = '@nkostadinov/taxonomy/migrations/template/tag.php';

    public function addTerm($object_id, $params)
    {
        foreach($params as $item) {
            $term = $this->getTaxonomyTerm($item);
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            $query = new Query();
            if (!$query->from($this->table)->where($data)->exists($this->getDb())) {
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
}
