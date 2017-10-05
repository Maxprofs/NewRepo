<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetConfiguration;

/**
 * JetConfigurationSearch represents the model behind the search form about `app\models\JetConfiguration`.
 */
class JetConfigurationSearch extends JetConfiguration
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['api_host', 'api_user', 'api_password', 'fullfilment_node_id', 'merchant_email', 'jet_token'], 'safe'],
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
        $query = JetConfiguration::find();

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
        ]);

        $query->andFilterWhere(['like', 'api_host', $this->api_host])
            ->andFilterWhere(['like', 'api_user', $this->api_user])
            ->andFilterWhere(['like', 'api_password', $this->api_password])
            ->andFilterWhere(['like', 'fullfilment_node_id', $this->fullfilment_node_id])
            ->andFilterWhere(['like', 'merchant_email', $this->merchant_email])
            ->andFilterWhere(['like', 'jet_token', $this->jet_token]);

        return $dataProvider;
    }
}
