<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\JetShopDetails;

/**
 * JetShopDetailsSearch represents the model behind the search form about `backend\models\JetShopDetails`.
 */
class JetShopDetailsSearch extends JetShopDetails
{
    public $customWhere = '';
    // public $attribute ;
    public function setCustomWhere($where){
        $this->customWhere = $where;
    }
    /**
     * @inheritdoc
     */
    public $review_to, $review_from, $live_to, $live_from, $order_to, $order_from;
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['shop_url', 'shop_name', 'email', 'country_code', 'currency', 'install_status', 'installed_on', 'expired_on', 'purchased_on', 'purchase_status','review_to','review_from','live_to','live_from','order_to','order_from','seller_username','seller_password'], 'safe'],
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
        if($this->customWhere!=''){
            $query = JetShopDetails::find()->where($this->customWhere);
        }else{
            $query = JetShopDetails::find();
        }

        // add conditions that should always apply here

        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 50;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' =>  ['pageSize' => $pageSize],
        	'sort'=> ['defaultOrder' => ['merchant_id'=>SORT_ASC]],
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
            'installed_on' => $this->installed_on,
            'expired_on' => $this->expired_on,
            'purchased_on' => $this->purchased_on,
        ]);

        $query->andFilterWhere(['like', 'shop_url', $this->shop_url])
            ->andFilterWhere(['like', 'shop_name', $this->shop_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'country_code', $this->country_code])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'seller_username', $this->seller_username])
            ->andFilterWhere(['like', 'seller_password', $this->seller_password])
            ->andFilterWhere(['like', 'install_status', $this->install_status])
            ->andFilterWhere(['like', 'purchase_status', $this->purchase_status]);


        if(isset($this->review_from)  && $this->review_from != "" && isset($this->review_to) && $this->review_to != ""){
            $to = $from = "";
            $to = $this->review_to;
            $from = $this->review_from;
            $query->andWhere('`merchant_id` in(SELECT `merchant_id`  FROM `jet_product` where `status`="Under Jet Review" GROUP BY `merchant_id` HAVING  COUNT(*) between '.$from.' and '.$to.')');
        } 

        if(isset($this->live_from) && $this->live_from != "" && isset($this->live_to) && $this->live_to != ""){
            $to = $from = "";
            $to = $this->live_to;
            $from = $this->live_from;
            $query->andWhere('`merchant_id` in(SELECT `merchant_id`  FROM `jet_product` where `status`="Available for Purchase"  GROUP BY `merchant_id` HAVING  COUNT(*) between '.$from.' and '.$to.')');
        }  

        if(isset($this->order_from)  && $this->order_from != "" && isset($this->order_to) && $this->order_to != ""){
            $to = $from = "";
            $to = $this->order_to;
            $from = $this->order_from;
            $query->andWhere('`merchant_id` in(SELECT `merchant_id` FROM `jet_order_detail` where `status`="complete"  GROUP BY `merchant_id` HAVING  COUNT(*) between '.$from.' and '.$to.')');
        }
        // var_dump($query->prepare(\Yii::$app->db->queryBuilder)->createCommand()->rawSql);die();
        //var_dump($dataProvider->getModels());die('hh');
        return $dataProvider;
    }
}
