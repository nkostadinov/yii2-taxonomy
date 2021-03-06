<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model nkostadinov\taxonomy\models\TaxonomyDef */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Taxonomy Defs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="taxonomy-def-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php // Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'class',
            'created_at',
            'total_count',
            'data_table',
            'ref_table',
        ],
    ]) ?>

    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $termProvider,
        'columns' => [
            'id',
            'term',
            'total_count'
        ]
    ]) ?>
    <?php Pjax::end(); ?>

</div>
