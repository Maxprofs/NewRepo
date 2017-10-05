<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetOrderDetail;

/**
 * JetOrderDetailSearch represents the model behind the search form about `app\models\JetOrderDetail`.
 */
class JetOrderDetailSearch extends JetOrderDetail
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['order_item_id', 'merchant_order_id', 'merchant_sku', 'deliver_by','shopify_order_name', 'shopify_order_id', 'status', 'order_data', 'shipment_data', 'reference_order_id'], 'safe'],
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
       // $query = JetOrderDetail::find();
       
    	$merchant_id=\Yii::$app->user->identity->id;
    	$query = JetOrderDetail::find()->where(['merchant_id' => $merchant_id]);
    	
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
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
            // 'shopify_order_name' => $this->shopify_order_name,
        ]);

        $query->andFilterWhere(['like', 'order_item_id', $this->order_item_id])
            ->andFilterWhere(['like', 'merchant_order_id', $this->merchant_order_id])
            ->andFilterWhere(['like', 'merchant_sku', $this->merchant_sku])
            ->andFilterWhere(['like', 'deliver_by', $this->deliver_by])
             ->andFilterWhere(['like', 'shopify_order_name', $this->shopify_order_name])
            ->andFilterWhere(['like', 'shopify_order_id', $this->shopify_order_id])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'order_data', $this->order_data])
            ->andFilterWhere(['like', 'shipment_data', $this->shipment_data])
            ->andFilterWhere(['like', 'reference_order_id', $this->reference_order_id]);
           
        return $dataProvider;
    }
}
