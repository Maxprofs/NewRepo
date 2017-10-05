<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\NeweggShopDetail;

/**
 * NeweggShopDetailSearch represents the model behind the search form about `backend\models\NeweggShopDetail`.
 */
class NeweggShopDetailSearch extends NeweggShopDetail
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['shop_url', 'shop_name', 'email', 'token', 'country_code', 'currency', 'install_status', 'install_date', 'expire_date', 'purchase_date', 'purchase_status', 'client_data', 'uninstall_date', 'app_status'], 'safe'],
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
        $query = NeweggShopDetail::find()->andWhere(['<>','newegg_shop_detail.email', 'developer.cedcoss@gmail.com']);

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
            'install_date' => $this->install_date,
            'expire_date' => $this->expire_date,
            'purchase_date' => $this->purchase_date,
            'uninstall_date' => $this->uninstall_date,
        ]);

        $query->andFilterWhere(['like', 'shop_url', $this->shop_url])
            ->andFilterWhere(['like', 'shop_name', $this->shop_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'country_code', $this->country_code])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'install_status', $this->install_status])
            ->andFilterWhere(['like', 'purchase_status', $this->purchase_status])
            ->andFilterWhere(['like', 'client_data', $this->client_data])
            ->andFilterWhere(['like', 'app_status', $this->app_status]);

        return $dataProvider;
    }
}
