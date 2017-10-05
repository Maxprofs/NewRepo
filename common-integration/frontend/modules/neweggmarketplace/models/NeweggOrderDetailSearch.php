<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\neweggmarketplace\models\NeweggOrderDetail;

/**
 * NeweggOrderDetailSearch represents the model behind the search form about `frontend\modules\neweggmarketplace\models\NeweggOrderDetail`.
 */
class NeweggOrderDetailSearch extends NeweggOrderDetail
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'invoice_number'], 'integer'],
            [['seller_id', 'order_number', 'shopify_order_name','order_data', 'sku','order_status_description', 'order_date'], 'safe'],
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
        $query = NeweggOrderDetail::find()->where(['merchant_id' => MERCHANT_ID]);

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
            'invoice_number' => $this->invoice_number,
            'order_date' => $this->order_date,
        ]);

        $query->andFilterWhere(['like', 'seller_id', $this->seller_id])
            ->andFilterWhere(['like', 'order_number', $this->order_number])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'shopify_order_name', $this->shopify_order_name])
            ->andFilterWhere(['like', 'order_data', $this->order_data])
            ->andFilterWhere(['like', 'order_status_description', $this->order_status_description]);

        return $dataProvider;
    }
}
