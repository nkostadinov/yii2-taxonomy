<?php

namespace nkostadinov\taxonomy\models;

use Yii;

/**
 * This is the model class for table "taxonomy_terms".
 *
 * @property integer $id
 * @property integer $taxonomy_id
 * @property string $term
 * @property string $total_count
 *
 * @property Taxonomy $taxonomy
 */
class TaxonomyTerms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'taxonomy_terms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['taxonomy_id', 'term', 'total_count'], 'required'],
            [['taxonomy_id', 'total_count'], 'integer'],
            [['term'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'taxonomy_id' => 'Taxonomy ID',
            'term' => 'Term',
            'total_count' => 'Total Count',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaxonomy()
    {
        return $this->hasOne(TaxonomyDef::className(), ['id' => 'taxonomy_id']);
    }
}
