<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:31 ч.
 */

namespace nkostadinov\taxonomy\interfaces;


interface ITaxonomyTermInterface {

    public function install();

    public function addTerm($object_id, $value);

} 