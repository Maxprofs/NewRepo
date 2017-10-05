<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetRefund;

/**
 * JetRefundSearch represents the model behind the search form about `app\models\JetRefund`.
 */
class JetRefundSearch extends JetRefund
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['merchant_order_id','order_item_id', 'quantity_returned', 'refund_quantity', 'refund_reason', 'refund_feedback', 'refund_tax', 'refund_shippingcost', 'refund_shippingtax', 'refund_amount', 'refund_id', 'refund_status'], 'safe'],
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
        //$query = JetRefund::find();
    	//	$query = JetRefund::find(['merchant_id'=>$merchant_id])->all();
        $merchant_id=\Yii::$app->user->identity->id;
        $query = JetRefund::find()->where(['merchant_id' => $merchant_id]);

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
        ]);

        $query->andFilterWhere(['like', 'order_item_id', $this->order_item_id])
            ->andFilterWhere(['like', 'quantity_returned', $this->quantity_returned])
            ->andFilterWhere(['like', 'refund_quantity', $this->refund_quantity])
            ->andFilterWhere(['like', 'refund_reason', $this->refund_reason])
            ->andFilterWhere(['like', 'refund_feedback', $this->refund_feedback])
            ->andFilterWhere(['like', 'refund_tax', $this->refund_tax])
            ->andFilterWhere(['like', 'refund_shippingcost', $this->refund_shippingcost])
            ->andFilterWhere(['like', 'refund_shippingtax', $this->refund_shippingtax])
            ->andFilterWhere(['like', 'merchant_order_id', $this->merchant_order_id])
           /*  ->andFilterWhere(['like', 'refund_merchantid', $this->refund_merchantid]) */
            ->andFilterWhere(['like', 'refund_amount', $this->refund_amount])
            ->andFilterWhere(['like', 'refund_id', $this->refund_id])
            ->andFilterWhere(['like', 'refund_status', $this->refund_status]);

        return $dataProvider;
    }
}
