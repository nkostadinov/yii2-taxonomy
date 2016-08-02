<?php
/**
 * @author Nikola Kostadinov<nikolakk@gmail.com>
 * Date: 02.08.2016
 * Time: 09:11 ч.
 */

namespace nkostadinov\taxonomy\behaviors;


use nkostadinov\taxonomy\models\TaxonomyTerms;

class PropertyQueryBehavior extends BaseTermBehavior
{
    public function hasProp($name, $value = null)
    {
        $this->joinTables();


        if(!empty($name))
            $this->getQuery()
                ->andFilterWhere([ TaxonomyTerms::tableName() . '.term' => $name ]);

        if(!empty($value))
            $this->getQuery()
                ->andFilterWhere([ $this->taxonomy->table . '.value' => $value ]);

        return $this->getQuery();
    }
}