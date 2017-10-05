<?php

namespace frontend\modules\neweggcanada\models;

use frontend\modules\neweggcanada\components\Data;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\neweggcanada\models\NeweggOrderImportError;

/**
 * NeweggOrderImportErrorSearch represents the model behind the search form about `frontend\modules\neweggcanada\models\NeweggOrderImportError`.
 */
class NeweggOrderImportErrorSearch extends NeweggOrderImportError
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['order_number', 'error_reason', 'created_at', 'newegg_item_number'], 'safe'],
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
        $query = NeweggOrderImportError::find()->where(['merchant_id' => MERCHANT_ID]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'merchant_id' => $this->merchant_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'order_number', $this->order_number])
            ->andFilterWhere(['like', 'error_reason', $this->error_reason])
            ->andFilterWhere(['like', 'newegg_item_number', $this->newegg_item_number]);

        return $dataProvider;
    }
}
