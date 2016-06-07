<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */

echo "<?php\n";
?>

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\db\Migration;

class <?= $data['migration'] ?> extends Migration
{
    public function up()
    {
        $this->insert(TaxonomyDef::tableName(), 
            <?= var_export($data) ?>
        );

        $this->createTable('{{%<?= $data['data_table'] ?>}}', [
            'id' => $this->primaryKey(),
            'object_id' => $this->integer()->notNull(),
            'term_id' => $this->bigInteger()->notNull(),
            'value' => $this->string(),
        ]);

        $this->addForeignKey('fk_<?= $data['data_table'] ?>_<?= $data['ref_table'] ?>', '<?= $data['data_table'] ?>', 'object_id', '<?= $data['ref_table'] ?>', 'id', 'CASCADE');
        $this->addForeignKey('fk_<?= $data['data_table'] ?>_' . TaxonomyTerms::tableName(), '<?= $data['data_table'] ?>', 'term_id', TaxonomyTerms::tableName(), 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%<?= $data['data_table'] ?>}}');
    }
}