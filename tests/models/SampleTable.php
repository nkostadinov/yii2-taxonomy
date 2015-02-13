<?php

namespace nkostadinov\taxonomy\tests\models;

use nkostadinov\taxonomy\behaviors\TagQuery;
use Yii;

/**
 * This is the model class for table "sample_table".
 *
 * @property integer $id
 * @property string $name
 */
class SampleTable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sample_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string']
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
        ];
    }

    /**
     * @return TagQuery
     */
    public static function find()
    {
        $query = parent::find();
        $query->attachBehavior('taxonomy-tag', [
            'class' => \nkostadinov\taxonomy\behaviors\TagQuery::className(),
            'taxonomy' => \Yii::$app->taxonomy->getTerm('test-tag'),
        ]);
        return $query;
    }
}
