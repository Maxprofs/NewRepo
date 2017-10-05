<?php

namespace frontend\modules\jet\models;

use frontend\modules\jet\models\JetProductDetails;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * JetProductDetailsSearch represents the model behind the search form about `frontend\modules\jet\models\JetProductDetails`.
 */
class JetProductDetailsSearch extends JetProductDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'merchant_id', 'fulfillment_node'], 'integer'],
            [['title', 'product_type', 'description', 'jet_attributes', 'ASIN', 'mpn', 'error', 'status', 'updated_at'], 'safe'],
            [['price'], 'number'],
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
    	$merchant_id=\Yii::$app->user->identity->id;
        $query = JetProductDetails::find()->where(['merchant_id' => $merchant_id])->andWhere(['!=','fulfillment_node','']);

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
            'product_id' => $this->product_id,
            'merchant_id' => $this->merchant_id,
            'price' => $this->price,
            'fulfillment_node' => $this->fulfillment_node,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'product_type', $this->product_type])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'jet_attributes', $this->jet_attributes])
            ->andFilterWhere(['like', 'ASIN', $this->ASIN])
            ->andFilterWhere(['like', 'mpn', $this->mpn])
            ->andFilterWhere(['like', 'error', $this->error])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
