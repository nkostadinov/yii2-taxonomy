<?php

namespace nkostadinov\taxonomy\components\terms;

use insight\core\helpers\ArrayHelper;
use nkostadinov\taxonomy\components\interfaces\IHierarchicalTerm;
use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use Yii;
use yii\db\Query;

class CategoryTerm extends TagTerm implements IHierarchicalTerm
{
    public $templateFile = '@nkostadinov/taxonomy/migrations/template/category.php' ;

    public function getTerms($object_id, $name = [])
    {
        $query = (new Query())
            ->select(TaxonomyTerms::tableName() . '.term')
            ->from(TaxonomyTerms::tableName())
            ->innerJoin($this->table, $this->table . '.term_id = taxonomy_terms.id',
                [':object_id' => $object_id])
            ->andFilterWhere(['taxonomy_terms.term' => $name]);

        if ($object_id) {
            $query->onCondition("$this->table.object_id = $object_id");
        }

        $result = [];
        foreach($query->all() as $v) {
            $result[] = $v['term'];
        }

        return $result;
    }

    public function addTerm($object_id, $params)
    {
        $cachedParents = [];

        $addTerm = function ($parent, $item) use ($object_id, &$cachedParents) {
            $term = $this->getTaxonomyTerm($item);
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            if (array_key_exists($parent, $cachedParents)) {
                $term->parent_id = $cachedParents[$parent]->id;
            } else if (is_string($parent)) {
                $parentTerm = $this->getTaxonomyTerm($parent);
                $cachedParents[$parent] = $parentTerm;
                $term->parent_id = $parentTerm->id;
            }

            if (!(new Query())->from($this->table)->where($data)->exists(CategoryTerm::getDb())) {
                Yii::$app->db->transaction(function() use ($data, $term) {
                    if ($term->getDirtyAttributes(['parent_id'])) {
                        $term->save(false);
                    }
                    CategoryTerm::getDb()->createCommand()->insert($this->table, $data)->execute();

                    $term->updateCounters(['total_count' => 1]);
                    TaxonomyDef::updateAllCounters(['total_count' => 1], ['id' => $this->id]);
                });
            }
        };

        foreach ($params as $parent => $item) {
            if (is_array($item)) {
                foreach ($item as $child) {
                    $addTerm($parent, $child);
                }
            } else {
                $addTerm($parent, $item);
            }
        }
    }

    public function getParent($term)
    {
        $childTerm = $this->getTaxonomyTerm($term);
        if (!$childTerm || is_null($childTerm->parent_id)) {
            return null;
        }

        $parentTerm = (new Query())
            ->select('term')
            ->from(TaxonomyTerms::tableName())
            ->where("id = $childTerm->parent_id")
            ->one(self::getDb());

        return $parentTerm ? $parentTerm['term'] : null;
    }

    public function hasParent($term)
    {
        return $this->getParent($term) != null;
    }

    public function getChildren($term)
    {
        $parentTerm = $this->getTaxonomyTerm($term);
        if (!$parentTerm) {
            return [];
        }

        $query = (new Query())
            ->select('term')
            ->from(TaxonomyTerms::tableName())
            ->where("parent_id = $parentTerm->id");

        $result = [];
        foreach ($query->all(self::getDb()) as $row) {
            $result[] = $row['term'];
        }

        return $result;
    }
    
    public function hasChildren($term)
    {
        $parentTerm = $this->getTaxonomyTerm($term);
        if (!$parentTerm) {
            return false;
        }
        
        return (new Query())
            ->from(TaxonomyTerms::tableName())
            ->where("parent_id = $parentTerm->id")
            ->exists(self::getDb());
    }
}
