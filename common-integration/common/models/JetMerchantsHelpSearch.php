<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\JetMerchantsHelp;

/**
 * JetMerchantsHelpSearch represents the model behind the search form about `common\models\JetMerchantsHelp`.
 */
class JetMerchantsHelpSearch extends JetMerchantsHelp
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['merchant_name', 'merchant_store_name', 'merchant_email_id', 'subject', 'query', 'solution', 'status', 'time'], 'safe'],
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
        $query = JetMerchantsHelp::find();

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
            'time' => $this->time,
        ]);

        $query->andFilterWhere(['like', 'merchant_name', $this->merchant_name])
            ->andFilterWhere(['like', 'merchant_store_name', $this->merchant_store_name])
            ->andFilterWhere(['like', 'merchant_email_id', $this->merchant_email_id])
            ->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'query', $this->query])
            ->andFilterWhere(['like', 'solution', $this->solution])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
