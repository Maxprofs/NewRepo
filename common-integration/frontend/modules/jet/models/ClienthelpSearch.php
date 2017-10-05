<?php
namespace frontend\modules\jet\models;

use frontend\modules\jet\models\JetMerchatsHelp;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ClienthelpSearch represents the model behind the search form about `frontend\models\JetMerchatsHelp`.
 */
class ClienthelpSearch extends JetMerchatsHelp
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['merchant_store_name', 'merchant_email_id', 'subject', 'query', 'status', 'time'], 'safe'],
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
        $query = JetMerchatsHelp::find();

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

        $query->andFilterWhere(['like', 'merchant_store_name', $this->merchant_store_name])
            ->andFilterWhere(['like', 'merchant_email_id', $this->merchant_email_id])
            ->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'query', $this->query])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
