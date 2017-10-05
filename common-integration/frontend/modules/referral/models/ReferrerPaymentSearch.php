<?php

namespace frontend\modules\referral\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\referral\models\ReferrerPayment;
use frontend\modules\referral\components\Helper;

/**
 * ReferrerPaymentSearch represents the model behind the search form about `frontend\modules\referral\models\ReferrerPayment`.
 */
class ReferrerPaymentSearch extends ReferrerPayment
{
    public $username;
    public $shop_name;
    public $plan_type;
    public $status;
    public $recurring_data;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'payment_id', 'referrer_id', 'referral_id'], 'integer'],
            [['amount'], 'number'],
            [['type', 'comment', 'payment_date', 'app', 'status', 'username', 'shop_name', 'plan_type', 'status', 'recurring_data'], 'safe'],
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

        $query = ReferrerPayment::find()->select(['`referrer_payment`.*, `walmart_recurring_payment`.`plan_type`, `walmart_recurring_payment`.`status` as `recurring_paymnt_status`, `walmart_recurring_payment`.`recurring_data`, `user`.`username`, `user`.`shop_name`'])->where(['referrer_payment.referrer_id' => $referrerId]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        //$query->innerJoinWith(['user']);
        $subQuery = (new \yii\db\Query())->select('id,username,shop_name')->from('user');
        $query->leftJoin(['user' => $subQuery], 'user.id = walmart_recurring_payment.merchant_id');

        $query->joinWith(['walmart_recurring_payment']);
        /*$subQuery = (new \yii\db\Query())->select('id, plan_type as wal_plan_type, status as wal_status ,recurring_data as wal_recurring_data')->from('walmart_recurring_payment');
        $query->leftJoin(['walmart_payment' => $subQuery], 'walmart_payment.id = referrer_payment.payment_id');*/

        $query->andFilterWhere([
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'referrer_id' => $this->referrer_id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'app', $this->app])
            ->andFilterWhere(['like', 'status', $this->status]);

        $query->andFilterWhere(['like', 'status', $this->status]);

        $query->andFilterWhere(['like', 'user.username', $this->username])
              ->andFilterWhere(['like', 'user.shop_name', $this->shop_name]);

        $query->andFilterWhere(['like', 'walmart_recurring_payment.plan_type', $this->plan_type])
              ->andFilterWhere(['like', 'walmart_recurring_payment.status', $this->status]);
        //$query->andFilterWhere(['like', 'walmart_recurring_payment.recurring_data', '%'.$this->recurring_data.'%']);
        //var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);die();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $pageSize],
            'sort' => ['attributes' => ['id', 'payment_id', 'referrer_id', 'referral_id', 'amount', 'type', 'comment:ntext', 'payment_date', 'app','status']]
        ]);
        
        return $dataProvider;
    }
}
