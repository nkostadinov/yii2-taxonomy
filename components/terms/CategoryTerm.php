<?php

namespace nkostadinov\taxonomy\components\terms;

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use Yii;
use yii\base\InvalidCallException;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CategoryTerm extends HierarchicalTerm
{
    public $templateFile = '@nkostadinov/taxonomy/migrations/template/category.php';

    public function getTerms($object_id, $name = [])
    {
        $query = TaxonomyTerms::find()
            ->select(TaxonomyTerms::tableName() . '.term')
            ->innerJoin($this->table, $this->table . '.term_id = taxonomy_terms.id')
            ->andFilterWhere(['taxonomy_terms.term' => $name]);

        if ($object_id) {
            $query->onCondition("$this->table.object_id = $object_id");
        }

        return ArrayHelper::getColumn($query->all(), 'term');
    }

    /**
     * Add term/s with the ability to make hierarchies.
     *
     * The object_id can be skipped. In this case a hierarchy will be created without being attached to an object.
     *
     * $params can be a string or an array:
     *  - If string, this is considered to be a root of a hierarchy;
     *  - If array, if only filled with values, this means these are all roots of a hierarchy;
     *  - If array and key => value is given, the key is the parent, the root is the child.
     *
     * @param integer $object_id Id to and object. Not mandatory.
     * @param string|array $params Terms
     */
    public function addTerm($object_id, $params)
    {
        $cachedParents = [];

        $addTerm = function ($parent, $item) use ($object_id, &$cachedParents) {
            if ($this->detectLoop($parent, $item)) {
                throw new InvalidCallException('Loop detected! Cannot add parent as a child!');
            }

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

        $params = (array) $params;
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
