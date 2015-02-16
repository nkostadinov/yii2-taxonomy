<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model nkostadinov\taxonomy\models\TaxonomyDefSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="taxonomy-def-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'class') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'total_count') ?>

    <?php // echo $form->field($model, 'data_table') ?>

    <?php // echo $form->field($model, 'ref_table') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
