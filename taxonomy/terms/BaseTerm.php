<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:35 ч.
 */

namespace nkostadinov\taxonomy\terms;


use nkostadinov\taxonomy\interfaces\ITaxonomyTermInterface;
use yii\db\Migration;

abstract class BaseTerm implements ITaxonomyTermInterface
{
    protected  $table;
    protected  $is_multi = false;
    public $db = 'db';

    public abstract function addTerm($object_id, $value);

    public function install()
    {
        //apply migration
        if ($this->getDb()->schema->getTableSchema($this->table, true) === null) {
            $migration = new Migration();
            $migration->db = $this->db;

            $migration->createTable('', [
                'term_id' => 'int(11) NOT NULL',
            ]);
        }
    }

    /**
     * Return the db connection component.
     *
     * @return \yii\db\Connection
     */
    public function getDb()
    {
        return \Yii::$this->db;
    }
}