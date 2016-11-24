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
class PropertySupportBehavior extends Behavior
{
    public $property;

    private $_allowedMethods = [
        'propertyTerm' => 'getPropertyTerm',
        'properties' => 'getProperties',
    ];

    public function canGetProperty($name, $checkVars = true)
    {
        if (array_key_exists($name, $this->_allowedMethods)) {
            return true;
        }

        return false;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_allowedMethods)) {
            return $this->{$this->_allowedMethods[$name]}();
        }

        return false;
    }

    public function hasMethod($name)
    {
        return in_array($name, $this->_allowedMethods);
    }

    public function getPropertyTerm()
    {
        return Yii::$app->taxonomy->getTerm($this->property);
    }

    public function getProperties($properties = [])
    {
        return $this->propertyTerm->getTerms($this->id, $properties);
    }
}
