<?php

namespace nkostadinov\taxonomy\components\terms;

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class CategoryTerm extends HierarchicalTerm
{
    public $templateFile = '@nkostadinov/taxonomy/migrations/template/category.php';

    /**
     * Assigns terms to an object.
     *
     * @param integer $object_id
     * @param integer|array $params The ID/IDs of the term/terms that need to be assigned. Can be integer or array of integers.
     * @return An array with the currently assigned TaxonomyTerms.
     */
    public function addTerm($object_id, $params)
    {
        $result = [];

        foreach (TaxonomyTerms::findAll((array) $params) as $term) {
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            if (!(new Query())->from($this->table)->where($data)->exists(CategoryTerm::getDb())) {
                Yii::$app->db->transaction(function() use ($data, $term, &$result) {
                    CategoryTerm::getDb()->createCommand()->insert($this->table, $data)->execute();

                    $term->updateCounters(['total_count' => 1]);
                    TaxonomyDef::updateAllCounters(['total_count' => 1], ['id' => $this->id]);

                    $result[] = $term;
                });
            }
        }

        return $result;
    }

    /**
     * Removes terms from an object.
     *
     * @param integer $object_id The id of the object.
     * @param array|integer $params An array of term IDs or term ID.
     * @return An array with the TaxonomyTerms objects that were removed.
     */
    public function removeTerm($object_id, $params = [])
    {
        $result = [];
        
        foreach(TaxonomyTerms::findAll((array) $params) as $term) {
            $data['term_id'] = $term->id;
            $data['object_id'] = $object_id;

            $query = new Query();
            if ($query->from($this->table)->where($data)->exists($this->getDb())) {
                Yii::$app->db->transaction(function() use ($data, $term, &$result) {
                    $this->getDb()->createCommand()->delete($this->table, $data)->execute();

                    $term->updateCounters(['total_count' => -1]);
                    Taxonomydef::updateAllCounters(['total_count' => -1], ['id' => $this->id]);

                    $result[] = $term;
                });
            }
        }

        return $result;
    }

    /**
     * Overwrites the existing terms of the object, but only with the children of the given parents.
     *
     * $params is an array in the form [$parent_id => [$children], $parent_id2 => [$children2]].
     * The children of those parents will replace the existing terms where the given object is assigned.
     *
     * @param integer $object_id The object id whose terms are changed.
     * @param array $params The replacement
     * @return An array with the TaxonomyTerms set
     */
    public function setTerms($object_id, $params = [])
    {
        $result = [];

        foreach (TaxonomyTerms::findAll(array_keys($params)) as $parent) {
            $parentsChildren = $this->getChildren($parent->id);
            $this->removeTerm($object_id, ArrayHelper::getColumn($parentsChildren, 'id'));
            $result = array_merge($result, $this->addTerm($object_id, $params[$parent->id]));
        }

        return $result;
    }

    /**
     * Creates a new category with the following specifics:
     *  - If $parent is string - creates a root category without any children.
     *  - If parent is integer (meaning an id of a term) and:
     *     - $children is string - creates new category and assigns it to that parent;
     *     - $children is array of strings - creates new categories and assigns them to that parent;
     *
     * @param string|integer $parent
     * @param string|array $children
     * @return TaxonomyTerms|array Returns the objects created
     * @throws InvalidParamException If a parameter of a wrong type is given
     * @throws NotFoundHttpException If a parent with a given id is not found
     */
    public function createCategory($parent, $children = [])
    {
        if (is_string($parent)) {
            return $this->getTaxonomyTerm($parent);
        }

        if (!is_int($parent)) {
            throw new InvalidParamException('$parent must be integer!');
        }

        if (!is_string($children) && !is_array($children)) {
            throw new InvalidParamException('$children must be of type string or array!');
        }

        if (!TaxonomyTerms::find()->where(['id' => $parent])->exists()) {
            throw new NotFoundHttpException("Parent with id '$id' does not exist!");
        }

        $children = (array) $children;
        $result = [];
        foreach ($children as $child) {
            Yii::$app->db->transaction(function() use ($parent, $child, &$result) {
                if (!is_string($child)) {
                    throw new InvalidParamException('$children must contain only string values!');
                }

                $term = $this->getTaxonomyTerm($child);
                $term->parent_id = $parent;
                $term->save(false);

                $result[] = $term;
            });
        }

        return $result;
    }

    /**
     * @param integer $termId
     * @return TaxonomyTerms
     */
    public function getParent($termId)
    {
        $childTerm = TaxonomyTerms::findOne($termId);
        if (!$childTerm || is_null($childTerm->parent_id)) {
            return null;
        }

        return TaxonomyTerms::findOne(['id' => $childTerm->parent_id]);
    }

    /**
     * @param integer $termId
     * @return boolean
     */
    public function hasParent($termId)
    {
        return $this->getParent($termId) != null;
    }

    /**
     * @param integer $termId
     * @return array An array of TaxonomyTerms
     */
    public function getChildren($termId)
    {
        return TaxonomyTerms::findAll(['parent_id' => $termId]);
    }

    /**
     * @param integer $termId
     * @return boolean
     */
    public function hasChildren($termId)
    {
        return TaxonomyTerms::find()
            ->where("parent_id = $termId")
            ->exists(self::getDb());
    }
}
