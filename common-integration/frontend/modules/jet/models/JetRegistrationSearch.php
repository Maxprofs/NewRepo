<?php

namespace frontend\modules\jet\models;

use frontend\modules\jet\models\JetRegistration;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * JetRegistrationSearch represents the model behind the search form about `frontend\modules\jet\models\JetRegistration`.
 */
class JetRegistrationSearch extends JetRegistration
{
    /**
     * @inheritdoc
     */
    public $merchant,$installed_on,$expired_on;
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['name', 'shipping_source', 'other_shipping_source', 'mobile', 'email', 'reference', 'already_selling', 'previous_api_provider_name', 'is_uninstalled_previous', 'agreement', 'other_reference','merchant','installed_on','expired_on'], 'safe'],
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
        $query = JetRegistration::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->joinWith('jet_shop_details');
        $query->joinWith('jet_configuration');
        $query->andFilterWhere([
            'id' => $this->id,
            'jet_shop_details.merchant_id' => $this->merchant_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'shipping_source', $this->shipping_source])
            ->andFilterWhere(['like', 'other_shipping_source', $this->other_shipping_source])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'merchant', $this->merchant])
            ->andFilterWhere(['like', 'installed_on', $this->installed_on])
            ->andFilterWhere(['like', 'expired_on', $this->expired_on])
            ->andFilterWhere(['like', 'reference', $this->reference])
            ->andFilterWhere(['like', 'already_selling', $this->already_selling])
            ->andFilterWhere(['like', 'previous_api_provider_name', $this->previous_api_provider_name])
            ->andFilterWhere(['like', 'is_uninstalled_previous', $this->is_uninstalled_previous])
            ->andFilterWhere(['like', 'agreement', $this->agreement])
            ->andFilterWhere(['like', 'other_reference', $this->other_reference]);

        return $dataProvider;
    }
}
