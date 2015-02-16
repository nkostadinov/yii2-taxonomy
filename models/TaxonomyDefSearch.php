<?php

namespace nkostadinov\taxonomy\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use nkostadinov\taxonomy\models\TaxonomyDef;

/**
 * TaxonomyDefSearch represents the model behind the search form about `nkostadinov\taxonomy\models\TaxonomyDef`.
 */
class TaxonomyDefSearch extends TaxonomyDef
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'total_count'], 'integer'],
            [['name', 'class', 'created_at', 'data_table', 'ref_table'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TaxonomyDef::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'total_count' => $this->total_count,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'class', $this->class])
            ->andFilterWhere(['like', 'data_table', $this->data_table])
            ->andFilterWhere(['like', 'ref_table', $this->ref_table]);

        return $dataProvider;
    }
}
