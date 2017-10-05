<?php

namespace frontend\modules\referral\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\referral\models\ReferralUser;
use frontend\modules\referral\components\Helper;

/**
 * ReferralUserSearch represents the model behind the search form about `frontend\modules\referral\models\ReferralUser`.
 */
class ReferralUserSearch extends ReferralUser
{
    public $username;
    public $shop_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'referrer_id', 'merchant_id'], 'integer'],
            [['status', 'installation_date', 'payment_date', 'app', 'username', 'shop_name'], 'safe'],
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
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 50;
        $referrerId = Helper::getCurrentReferrerId();

        $query = ReferralUser::find()->where(['referral_user.referrer_id' => $referrerId]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $pageSize],
            'sort' => ['attributes' => ['id', 'username', 'shop_name', 'app', 'installation_date', 'payment_date']]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $subQuery = (new \yii\db\Query())->select('id,username,shop_name')->from('user');
        $query->innerJoin(['user' => $subQuery], 'user.id = referral_user.merchant_id');


        $query->andFilterWhere([
            'id' => $this->id,
            'referrer_id' => $this->referrer_id,
            'merchant_id' => $this->merchant_id,
            'installation_date' => $this->installation_date,
            'payment_date' => $this->payment_date,
            'app' => $this->app,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
              ->andFilterWhere(['like', 'user.username', $this->username])
              ->andFilterWhere(['like', 'user.shop_name', $this->shop_name]);
        
        return $dataProvider;
    }
}
