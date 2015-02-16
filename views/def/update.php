<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model nkostadinov\taxonomy\models\TaxonomyDef */

$this->title = 'Update Taxonomy Def: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Taxonomy Defs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="taxonomy-def-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'definitions' => $definitions,
    ]) ?>

</div>
