<?php
/**
 * Created by PhpStorm.
 * User: Nikola nb
 * Date: 19.10.2014
 * Time: 11:35 ч.
 */

namespace nkostadinov\taxonomy\components\terms;

use Aws\S3\Exception\AccessDeniedException;
use nkostadinov\taxonomy\components\interfaces\ITaxonomyTermInterface;
use nkostadinov\taxonomy\models\Taxonomy;
use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\base\Exception;
use yii\base\Object;

abstract class BaseTerm extends Object implements ITaxonomyTermInterface
{
    public $migrationPath = '@app/migrations';

    public $id;
    public $name; //the name of the term
    public $data_table;
    public $ref_table;
    public $is_multi = false;
    public $created_at;
    public $total_count;
    public $migration;

    public abstract function addTerm($object_id, $params);
    public abstract function removeTerm($object_id, $params = []);
    public abstract function getTerms($object_id, $name = []);

    public function listTerms()
    {
        $terms = TaxonomyTerms::find()
            ->select('term')
            ->where(['taxonomy_id' => $this->id])
            ->asArray()
            ->all();

        return \yii\helpers\ArrayHelper::getColumn($terms, 'term');
    }

    public function isInstalled()
    {
        return \Yii::$app->db->getTableSchema($this->getTable(), true) !== null;
    }

    public function install()
    {
        $this->createMigration();
    }

    public function uninstall()
    {
        //drop the data table
        //$this->getDb()->createCommand()->dropTable($this->getTable())->execute();
        //delete the term itself
        $model = TaxonomyDef::findOne($this->id);
        $model->delete();
    }

    /**
     * Return the db connection component.
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return \Yii::$app->db;
    }

    public function canInstall() {
        if(!$this->getTable())
            return 'Missing "table" property';
        return true;
    }

    public function getTaxonomyTerm($name, $create = true)
    {
        $term = TaxonomyTerms::findOne(['term'=>$name, 'taxonomy_id' => $this->id]);
        if($create and !isset($term))
        {
            $term = new TaxonomyTerms();
            $term->taxonomy_id = $this->id;
            $term->term = $name;
            $term->total_count = 0;
            $term->save();
        }
        return $term;
    }

    public function getRefTableName()
    {
        if(strpos($this->refTable, '\\') === FALSE) //not an AR class but a table name
            return $this->refTable;
        else
            return call_user_func([$this->refTable, 'tableName']);
    }

    public function getTable()
    {
        return $this->data_table;
    }

    public function getRefTable()
    {
        return $this->ref_table;
    }

    public function getMigrationFile()
    {
        if (!preg_match('/^\w+$/', $this->name)) {
            throw new Exception('The migration name should contain letters, digits and/or underscore characters only.');
        }
        $name = 'm' . gmdate('ymd_His') . '_' . $this->name;
        return $name;
    }

    public function createMigration()
    {

        $name = $this->getMigrationFile();
        $file = \Yii::getAlias($this->migrationPath . DIRECTORY_SEPARATOR . $name . '.php');

        $data = get_object_vars($this);
        $data['migration'] = $name;
        $this->migration = $name;
        $data['class'] = get_class($this);
        $content = \Yii::$app->getView()->renderFile(\Yii::getAlias($this->templateFile), [ 'data' => $data ]);
        file_put_contents($file, $content);
    }
}