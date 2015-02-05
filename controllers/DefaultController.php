<?php

namespace nkostadinov\taxonomy\controllers;

use nkostadinov\taxonomy\models\TaxonomyTerms;
use nkostadinov\taxonomy\Taxonomy;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Migration;
use yii\db\Schema;
use yii\helpers\Url;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        if(!$this->isInstalled())
            $this->redirect($this->module->id . '/' . $this->id . '/install');

        foreach($this->getComponent()->config as $name=>$cfg) {
            $term = $this->getComponent()->getTerm($name);
            $data[] = [
                'name' => $name,
                'class' => $cfg['class'],
                'table' => $cfg['table'],
                'refTable' => $cfg['refTable'],
                'installed' => $term->isInstalled(),
                'term' => \nkostadinov\taxonomy\models\Taxonomy::findOne(['name' => $name]),
            ];
        }
        $terms = new ArrayDataProvider([
            'allModels' => $data,
        ]);

        return $this->render('index', compact('terms'));
    }

    public function isInstalled()
    {
        return \Yii::$app->db->getTableSchema(\nkostadinov\taxonomy\models\Taxonomy::tableName());
    }

    public function actionInstall()
    {
        if(!$this->isInstalled() and \Yii::$app->request->isPost) {
            //start installation
            if($this->getComponent()) {
                $migration = new Migration();
                $migration->createTable(\nkostadinov\taxonomy\models\Taxonomy::tableName(), [
                    'id' => Schema::TYPE_PK,
                    'name' => Schema::TYPE_STRING,
                    'class' => Schema::TYPE_STRING,
                    'created_at' => Schema::TYPE_TIMESTAMP . ' DEFAULT CURRENT_TIMESTAMP',
                    'total_count' => Schema::TYPE_BIGINT . ' DEFAULT 0',
                ]);

                $migration->createIndex('unq_name', \nkostadinov\taxonomy\models\Taxonomy::tableName(), 'name', true);

                $migration->createTable(TaxonomyTerms::tableName(), [
                    'id' => Schema::TYPE_BIGPK,
                    'taxonomy_id' => Schema::TYPE_INTEGER,
                    'term' => Schema::TYPE_STRING,
                    'total_count' => Schema::TYPE_BIGINT . ' DEFAULT 0',
                    'parent_id' => Schema::TYPE_BIGINT,
                ]);

                $migration->addForeignKey('fk_TaxonomyTerm_Taxonomy', TaxonomyTerms::tableName(), 'taxonomy_id',
                    \nkostadinov\taxonomy\models\Taxonomy::tableName(), \nkostadinov\taxonomy\models\Taxonomy::primaryKey());

                $this->redirect($this->module->id . '/' . $this->id . '/index');
            } else
                throw new InvalidConfigException("Cannot find taxonomy component({$this->module->component})");
        }
        return $this->render('install');
    }

    public function actionInstallterm($term)
    {
        $term = $this->getComponent()->getTerm($term);
        $term->install();
    }

    public function getComponent()
    {
        if(\Yii::$app->has($this->module->component))
            return \Yii::$app->{$this->module->component};
        else
            throw new InvalidConfigException("Cannot find taxonomy component({$this->module->component})");
    }
}
