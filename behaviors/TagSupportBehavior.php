<?php

namespace nkostadinov\taxonomy\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * @author Nikolay Traykov
 */
class TagSupportBehavior extends Behavior
{
    public $taxonomies = [];

    private $_tags = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    public function afterSave(AfterSaveEvent $event)
    {
        foreach ($this->_tags as $name => $value) {
            /*
              The cast is there because if using Selectize for example and
              the tags input is empty, Selectize will return an empty string
             */
            $this->taxonomies[$name]->setTerms($this->owner->id, (array) $value);
        }
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if (isset($this->taxonomies[$name])) {
            return true;
        }

        return false;
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name);
    }

    public function __get($name)
    {
        $this->loadTagTaxonomy($name);
        if (!$this->owner->isNewRecord && !isset($this->_tags[$name])) {
            $this->_tags[$name] = $this->taxonomies[$name]->getTerms($this->owner->id);
        }

        return $this->_tags[$name];
    }

    public function __set($name, $value)
    {
        if (isset($this->taxonomies[$name])) {
            $this->loadTagTaxonomy($name);
            $this->_tags[$name] = $value;
        }
    }

    private function loadTagTaxonomy($name)
    {
        if (is_string($this->taxonomies[$name])) {
            $this->taxonomies[$name] = Yii::$app->taxonomy->getTerm($this->taxonomies[$name]);
        }
    }
}
