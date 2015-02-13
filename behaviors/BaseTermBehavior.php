<?php
/**
  * User: Phreak
 * Date: 13.02.2015
 * Time: 09:31 ч.
 */

namespace nkostadinov\taxonomy\behaviors;


use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\base\Behavior;

class BaseTermBehavior extends Behavior
{
    /** @property BaseTerm $taxonomy */
    public $taxonomy;

    protected function joinTables()
    {
        /** @var ActiveRecord $model */
        $model = new $this->owner->modelClass();

        /** @var ActiveRecord $this->owner */
        $this->getQuery()
            ->innerJoin($this->taxonomy->table, $this->taxonomy->table . '.object_id = ' . $model::tableName() . '.id')
            ->innerJoin(TaxonomyTerms::tableName(), TaxonomyTerms::tableName() . '.id = ' . $this->taxonomy->table . '.term_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getQuery()
    {
        return $this->owner;
    }
}