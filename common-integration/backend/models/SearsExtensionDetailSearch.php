<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\SearsExtensionDetail;

/**
 * SearsExtensionDetailSearch represents the model behind the search form about `backend\models\SearsExtensionDetail`.
 */
class SearsExtensionDetailSearch extends SearsExtensionDetail
{
	public $customWhere = '';
	// public $attribute ;
	public function setCustomWhere($where){
		$this->customWhere = $where;
	}
	public $review_to, $review_from, $live_to, $live_from, $order_to, $order_from,$shop_url,$email,$shop_name;
	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['install_date', 'date', 'expire_date','shop_url', 'shop_name', 'email' ,'status', 'app_status', 'uninstall_date','panel_password','panel_username'], 'safe'],
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
    		$query = SearsExtensionDetail::find()->where($this->customWhere);
    	}else{
    		$query = SearsExtensionDetail::find();
    	}
    	
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

        $query->andFilterWhere([
            'id' => $this->id,
            'sears_shop_details.merchant_id' => $this->merchant_id,
            'install_date' => $this->install_date,
            'date' => $this->date,
            'expire_date' => $this->expire_date,
            'uninstall_date' => $this->uninstall_date,
        ]);
        $query->joinWith(['sears_shop_details']);

        $query->andFilterWhere(['like', 'sears_extension_detail.status', $this->status])
            ->andFilterWhere(['like', 'app_status', $this->app_status])
        	->andFilterWhere(['like', 'sears_shop_details.shop_url', $this->shop_url])
            ->andFilterWhere(['like', 'sears_shop_details.shop_name', $this->shop_name])
            ->andFilterWhere(['like', 'panel_username', $this->panel_username])
            ->andFilterWhere(['like', 'panel_password', $this->panel_password])
            ->andFilterWhere(['like', 'email', $this->email]);
        
            if(isset($this->live_from) && $this->live_from != "" && isset($this->live_to) && $this->live_to != ""){
            	$to = $from = "";
            	$to = $this->live_to;
            	$from = $this->live_from;
            	$query->andWhere('`merchant_id` in(SELECT `merchant_id`  FROM `sears_product` where `status`="PUBLISHED"  GROUP BY `merchant_id` HAVING  COUNT(*) between '.$from.' and '.$to.')');
            }
            
            if(isset($this->order_from)  && $this->order_from != "" && isset($this->order_to) && $this->order_to != ""){
            	$to = $from = "";
            	$to = $this->order_to;
            	$from = $this->order_from;
            	$query->andWhere('`merchant_id` in(SELECT `merchant_id` FROM `sears_order_detail` where `status`="Closed"  GROUP BY `merchant_id` HAVING  COUNT(*) between '.$from.' and '.$to.')');
            }   
        return $dataProvider;
    }
}
