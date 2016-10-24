<?php

namespace nkostadinov\taxonomy\components\terms;

/**
 * @author ntraykov
 */
abstract class HierarchicalTerm extends BaseTerm
{
    protected function detectLoop($parent, $child)
    {
        if ($parent === $child) {
            return true;
        }

        foreach ($this->getChildren($child) as $grandChild) {
            if ($this->detectLoop($parent, $grandChild)) {
                return true;
            }
        }

        return false;
    }

    abstract public function getParent($term);
    abstract public function hasParent($term);

    abstract public function getChildren($term);
    abstract public function hasChildren($term);
}
