<?php

namespace frontend\modules\neweggcanada\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\neweggcanada\models\NeweggCourtesyrefundDetail;

/**
 * NeweggCourtesyrefundDetailSearch represents the model behind the search form about `frontend\modules\neweggcanada\models\NeweggCourtesyrefundDetail`.
 */
class NeweggCourtesyrefundDetailSearch extends NeweggCourtesyrefundDetail
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'order_number', 'invoice_number', 'edit_user_name'], 'integer'],
            [['seller_id', 'courtesy_refund_id', 'reason', 'note_to_customer', 'status', 'is_newegg_refund', 'in_user_name', 'in_date', 'edit_date'], 'safe'],
            [['order_amount', 'refund_amount'], 'number'],
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
        $query = NeweggCourtesyrefundDetail::find();

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
            'order_number' => $this->order_number,
            'order_amount' => $this->order_amount,
            'invoice_number' => $this->invoice_number,
            'refund_amount' => $this->refund_amount,
            'in_date' => $this->in_date,
            'edit_user_name' => $this->edit_user_name,
            'edit_date' => $this->edit_date,
        ]);

        $query->andFilterWhere(['like', 'seller_id', $this->seller_id])
            ->andFilterWhere(['like', 'courtesy_refund_id', $this->courtesy_refund_id])
            ->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'note_to_customer', $this->note_to_customer])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'is_newegg_refund', $this->is_newegg_refund])
            ->andFilterWhere(['like', 'in_user_name', $this->in_user_name]);

        return $dataProvider;
    }
}
