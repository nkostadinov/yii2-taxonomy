<?php

namespace nkostadinov\taxonomy\controllers;

use nkostadinov\taxonomy\models\TaxonomyDef;
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
        if(!$this->getComponent()->isInstalled())
            $this->redirect($this->module->id . '/' . $this->id . '/install');

        $taxonomy = new ActiveDataProvider([
            'query' => TaxonomyDef::find(),
        ]);

        return $this->render('index', compact('taxonomy'));
    }

    public function actionInstall()
    {
        if(!$this->getComponent()->isInstalled() and \Yii::$app->request->isPost) {
            //start installation
            if($this->getComponent()) {
                $this->getComponent()->install();

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
