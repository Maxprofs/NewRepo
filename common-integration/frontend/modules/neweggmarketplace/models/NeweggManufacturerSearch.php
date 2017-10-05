<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\neweggmarketplace\models\NeweggManufacturer;

/**
 * NeweggCategoryMapSearch represents the model behind the search form about `frontend\modules\neweggmarketplace\models\NeweggCategoryMap`.
 */
class NeweggManufacturerSearch extends NeweggManufacturer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['manufacturer_name', 'manufacturer_url','manufacturer_support_email','manufacturer_support_phone','manufacturer_support_url','status'], 'safe'],
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
        $query = NeweggManufacturer::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query->andFilterWhere([
            'merchant_id' => MERCHANT_ID]),
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
        ]);

        $query->andFilterWhere(['like', 'manufacturer_name', $this->manufacturer_name])
            ->andFilterWhere(['like', 'manufacturer_url', $this->manufacturer_url])
            ->andFilterWhere(['like', 'manufacturer_support_email', $this->manufacturer_support_email])
             ->andFilterWhere(['like', 'manufacturer_support_phone', $this->manufacturer_support_phone])
              ->andFilterWhere(['like', 'manufacturer_support_url', $this->manufacturer_support_url])
              ->andFilterWhere(['like', 'status', $this->status]);
        return $dataProvider;
    }
}
