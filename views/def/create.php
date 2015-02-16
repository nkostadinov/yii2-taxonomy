<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model nkostadinov\taxonomy\models\TaxonomyDef */

$this->title = 'Create Taxonomy Def';
$this->params['breadcrumbs'][] = ['label' => 'Taxonomy Defs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="taxonomy-def-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr/>
    <?= $this->render('_form', [
        'model' => $model,
        'definitions' => $definitions,
    ]) ?>

</div>
