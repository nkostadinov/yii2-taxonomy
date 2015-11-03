<?php

use nkostadinov\taxonomy\models\TaxonomyDef;
use nkostadinov\taxonomy\models\TaxonomyTerms;
use yii\db\Migration;

class m151031_160731_taxonomy extends Migration
{
    public function up()
    {
        $this->createTable(TaxonomyDef::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'class' => $this->string()->notNull(),
            'created_at' => $this->timestamp(),
            'total_count' => $this->bigInteger()->defaultValue(0),
            'data_table' => $this->string()->notNull(),
            'ref_table' => $this->string()->notNull(),
            'migration' => $this->string()
        ]);

        $this->createTable(TaxonomyTerms::tableName(), [
            'id' => $this->bigPrimaryKey(),
            'taxonomy_id' => $this->integer()->notNull(),
            'term' => $this->string(),
            'total_count' => $this->bigInteger()->defaultValue(0),
            'parent_id' => $this->bigInteger(),
        ]);

        if ($this->db->driverName === 'mysql') {
            $this->addForeignKey('fk_TaxonomyTerm_Taxonomy', TaxonomyTerms::tableName(), 'taxonomy_id',
                TaxonomyDef::tableName(), 'id');
        }
    }

    public function down()
    {
        $this->dropTable(TaxonomyTerms::tableName());
        $this->dropTable(TaxonomyDef::tableName());
    }
}
