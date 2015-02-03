<?php
    use yii\grid\DataColumn;
?>
<div class="taxonomy-default-index">
    <h1>Yii2 Taxonomy module</h1>
    <?=
    \yii\grid\GridView::widget([
        'dataProvider' => $terms,
        'emptyText' => 'No terms defined. Please add terms in config.',
        'columns' => [
            'name',
            'class',
            'table',
            'refTable',
            'total_count',
            [
                'label' => 'Installed ?',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    if(!isset($model['term']) or !$model['term']->created_at)
                        return \yii\helpers\Html::a('install', \yii\helpers\Url::to(['default/installterm', 'term' => $model['name']]));
                    else
                        return $model['term']->created_at;
                },
            ]
        ],
    ])

    ?>
</div>
