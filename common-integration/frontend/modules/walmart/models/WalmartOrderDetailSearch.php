<?php

namespace frontend\modules\walmart\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\walmart\models\WalmartOrderDetail;

/**
 * WalmartOrderDetailsSearch represents the model behind the search form about `frontend\modules\walmart\models\WalmartOrderDetails`.
 */
class WalmartOrderDetailSearch extends WalmartOrderDetail
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'shopify_order_id'], 'integer'],
            [['shopify_order_name','order_total', 'sku', 'purchase_order_id', 'order_data', 'shipment_data', 'created_at', 'status'], 'safe'],
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
        $query = WalmartOrderDetail::find()->where(['merchant_id' => MERCHANT_ID]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'merchant_id' => $this->merchant_id,
            'shopify_order_id' => $this->shopify_order_id,
        ]);

        $query->andFilterWhere(['like', 'shopify_order_name', $this->shopify_order_name])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'order_total', $this->order_total])
            ->andFilterWhere(['like', 'purchase_order_id', $this->purchase_order_id])
            ->andFilterWhere(['like', 'order_data', $this->order_data])
            ->andFilterWhere(['like', 'shipment_data', $this->shipment_data])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
