<?php

use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel nkostadinov\taxonomy\models\TaxonomyDefSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Taxonomy Defs';
$this->params['breadcrumbs'][] = $this->title;
$message = Yii::$app->session->getFlash('info');
?>
<div class="taxonomy-def-index">
    <?php if(isset($message))
        echo Alert::widget([
            'options' => [
                'class' => 'alert-info',
            ],
            'body' => nl2br($message),
        ]);
    ?>

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Taxonomy Definition', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'id',
            'name',
            'class',
            'created_at',
            'total_count',
            'data_table',
            'ref_table',
            'migration',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}', //{update}
            ],
        ],
    ]); ?>

</div>
