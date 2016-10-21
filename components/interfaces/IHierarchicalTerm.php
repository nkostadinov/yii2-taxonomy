<?php

namespace nkostadinov\taxonomy\components\interfaces;

/**
 * @author ntraykov
 */
interface IHierarchicalTerm
{
    public function getParent($term);
    public function hasParent($term);

    public function getChildren($term);
    public function hasChildren($term);
}
