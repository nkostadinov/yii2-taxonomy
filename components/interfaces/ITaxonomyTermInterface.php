<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:31 ч.
 */

namespace nkostadinov\taxonomy\components\interfaces;


interface ITaxonomyTermInterface {

    public function install();
    public function uninstall();

    public function addTerm($object_id, $params);
    public function removeTerm($object_id, $params = []);
    public function getTerms($object_id, $name = []);

} 