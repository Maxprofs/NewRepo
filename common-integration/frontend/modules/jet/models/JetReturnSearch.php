<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetReturn;

/**
 * JetReturnSearch represents the model behind the search form about `app\models\JetReturn`.
 */
class JetReturnSearch extends JetReturn
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','merchant_id'], 'integer'],
            [['order_reference_id','agreeto_return', 'status', 'returnid'], 'safe'],
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
       // $query = JetReturn::find();
    	$merchant_id=\Yii::$app->user->identity->id;
    	$query = JetReturn::find()->where(['merchant_id' => $merchant_id]);

        

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            //'qty_refunded' => $this->qty_refunded,
            'merchant_id' => $this->merchant_id,
        ]);

        $query->andFilterWhere(['like', 'order_reference_id', $this->order_reference_id])
            //->andFilterWhere(['like', 'order_item_id', $this->order_item_id])
            //->andFilterWhere(['like', 'return_refundfeedback', $this->return_refundfeedback])
            ->andFilterWhere(['like', 'agreeto_return', $this->agreeto_return])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'returnid', $this->returnid]);
            //->andFilterWhere(['like', 'amount', $this->amount])
            //->andFilterWhere(['like', 'shipping_cost', $this->shipping_cost])
            //->andFilterWhere(['like', 'shipping_tax', $this->shipping_tax])
           // ->andFilterWhere(['like', 'tax', $this->tax]);
        $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'sort'=> ['defaultOrder' => ['id' => SORT_DESC]]
                ]);    
        return $dataProvider;
    }
}
