<?php
/**
 * Date: 12.02.2015
 * Time: 22:33 ч.
 */

namespace nkostadinov\taxonomy\behaviors;

use nkostadinov\taxonomy\models\TaxonomyTerms;

class TagQuery extends BaseTermBehavior
{
    /**
     * @param array $tags
     * @return \yii\base\Component
     */
    public function hasTags($tags = [])
    {
        $this->joinTables();

        if(!empty($tags))
            $this->getQuery()
                ->andFilterWhere([ TaxonomyTerms::tableName() . '.term' => $tags ]);

        return $this->getQuery();
    }


}