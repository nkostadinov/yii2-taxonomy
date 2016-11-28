<?php

namespace nkostadinov\taxonomy\components\terms;

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use Yii;
use yii\base\InvalidCallException;
use yii\db\Query;
use yii\web\NotFoundHttpException;

class CategoryTerm extends HierarchicalTerm
{
    public $templateFile = '@nkostadinov/taxonomy/migrations/template/category.php';

    /**
     * Add term/s with the ability to make hierarchies.
     *
     * If object_id is null a hierarchy will be created without being attached to an object.
     *
     * $params can be a string or an array:
     *  - If string, a new root without children will be created;
     *  - If array with key => value is given, the key is the parent id, the value is the child.
     *
     * @param integer $object_id Id to an object. Not mandatory.
     * @param string|array $params Terms
     * @return TaxonomyTerm|array Returns the term created, if the $params param is string or array of terms if $params param is an array.
     */
    public function addTerm($object_id, $params)
    {
        $assignTermToObject = function ($term) use ($object_id) {
            if ($object_id) {
                $data['term_id'] = $term->id;
                $data['object_id'] = $object_id;

                if (!(new Query())->from($this->table)->where($data)->exists(CategoryTerm::getDb())) {
                    Yii::$app->db->transaction(function() use ($data, $term) {
                        CategoryTerm::getDb()->createCommand()->insert($this->table, $data)->execute();

                        $term->updateCounters(['total_count' => 1]);
                        TaxonomyDef::updateAllCounters(['total_count' => 1], ['id' => $this->id]);
                    });
                }
            }
        };

        if (is_string($params)) {
            $term = $this->getTaxonomyTerm($params);
            $assignTermToObject($term);
            
            return $term;
        }

        $result = [];
        foreach ($params as $parent_id => $children) {
            if (!($parent = TaxonomyTerms::findOne($parent_id))) {
                throw new NotFoundHttpException('The parent object does not exist!');
            }

            $children = (array) $children;
            foreach ($children as $child) {
                if (!is_string($child)) {
                    throw new InvalidCallException('Child must be string!');
                }
                
                if ($this->detectLoop($parent->term, $child)) {
                    throw new InvalidCallException('Loop detected! Cannot add parent as a child!');
                }

                $term = $this->getTaxonomyTerm($child);
                $term->parent_id = $parent_id;
                $term->save(false);
                $assignTermToObject($term);

                $result[] = $term;
            }
        }

        return $result;
    }

    public function setTerms($object_id, $params = [])
    {
        Yii::$app->db->transaction(function() use ($object_id, $params) {
            $this->removeTerm($object_id);
            foreach ($params as $parent_id => $children) {
                if (is_string($children)) { // This is a single parent
                    $this->addTerm($object_id, $children); // Create a new parent without any children
                } else {
                    $parent = $this->getTaxonomyTerm($parent_id);
                    $this->addTerm($object_id, $parent->term);
                    $this->addTerm($object_id, [$parent->id => $children]);
                }
            }
        });
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
