<?php
/**
 *
 */
use yii\helpers\Html;

?>
<h1>Taxonomy Component installation</h1>
<hr/>
<p>
    The component needs to be installed in order to use it.
</p>
<?= Html::beginForm('install') ?>
<?= Html::checkbox('terms', true, [ 'label' => 'Install terms']) ?>
<?= Html::submitButton('Start installation') ?>
<?= Html::endForm() ?>