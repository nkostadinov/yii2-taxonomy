<?php

namespace nkostadinov\taxonomy\models;

use Yii;

/**
 * This is the model class for table "taxonomy".
 *
 * @property integer $id
 * @property string $name
 * @property string $class
 * @property string $created_at
 * @property string $total_count
 *
 * @property TaxonomyTerms[] $taxonomyTerms
 */
class TaxonomyDef extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'taxonomy_def';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'class'], 'required'],
            [['created_at'], 'safe'],
            [['total_count'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'class' => 'Class',
            'created_at' => 'Created At',
            'total_count' => 'Total Count',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaxonomyTerms()
    {
        return $this->hasMany(TaxonomyTerms::className(), ['taxonomy_id' => 'id']);
    }
}
