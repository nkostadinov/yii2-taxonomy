<?php

namespace insight\core\traits;

use Yii;

/**
 * Helps models that use tags to get and set tag's values very easily.
 *
 * Advantages:
 *     - Simplicity
 *     - Centralized place of the code
 * 
 * Usage:
 *     - Add it to your model:
 *         class Shift extends ActiveRecord
 *         {
 *             use \insight\core\traits\TagSupport;
 *             ...
 *
 *     - Override the init() method of your model
 *         ...
 *         public function init()
 *         {
 *             $this->taxonomies['skills'] = 'userSkills'; // Just add the name of the taxonomy, nothing more
 *
 *             parent::init();
 *         }
 *         ...
 *
 * That's it!
 *
 * NOTE!!!
 * IF YOU OVERRIDE THE afterSave() METHOD IN YOU MODEL, YOU MUST CALL $this->saveTaxonomies() IN IT!!!
 *
 * @author Nikolay Traykov
 */
trait TagSupport
{
    private $_tags = [];

    protected $taxonomies = [];

    public function __get($name)
    {
        if (!isset($this->taxonomies[$name])) {
            return parent::__get($name);
        }

        $this->loadTaxonomy($name);
        if (!$this->isNewRecord && !isset($this->_tags[$name])) {
            $this->_tags[$name] = $this->taxonomies[$name]->getTerms($this->id);
        }

        return $this->_tags[$name];
    }

    public function __set($name, $value)
    {
        if (!isset($this->taxonomies[$name])) {
            parent::__set($name, $value);
        } else {
            $this->_tags[$name] = $value;
        }
    }

    protected function saveTaxonomies()
    {
        foreach ($this->_tags as $name => $value) {
            $this->taxonomies[$name]->removeTerm($this->id);
            // The cast is there because if the tags input is empty, selectize will return an empty string
            $this->taxonomies[$name]->addTerm($this->id, (array) $value);
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->saveTaxonomies();
        parent::afterSave($insert, $changedAttributes);
    }

    private function loadTaxonomy($name)
    {
        if (is_string($this->taxonomies[$name])) {
            $this->taxonomies[$name] = Yii::$app->taxonomy->getTerm($this->taxonomies[$name]);
        }
    }
}