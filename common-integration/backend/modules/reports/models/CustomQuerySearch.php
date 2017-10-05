<?php

namespace backend\modules\reports\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\JetShopDetails;
use yii\data\SqlDataProvider;

/**
 * JetShopDetailsSearch represents the model behind the search form about `backend\modules\reports\models\JetShopDetails`.
 */
class CustomQuerySearch extends JetShopDetails
{
    public $customQuery = '';
    public $whereQuery = '';
    public function setCustomQuery($query){
        $this->customQuery = $query;
    }

    public function andFilterWhere($filter = []){
    	
    	if(count($filter)>0 && !isset($filter[0])){
    		foreach($filter as $key=>$val){
    			if($val!=''){
    				$this->addWhere($key.'='."'{$val}'");
    			}
    		}
    	}
    	if(count($filter)==3 ){
    		if($filter[2]!=''){
	    		if($filter[0]=='like'){
	    			$this->addWhere($filter[1].' LIKE '."'%{$filter[2]}%'");
	    		}
	    		elseif($filter[0]=='lt'){
	    			$this->addWhere($filter[1].'<'."{$filter[2]}");
	    		}
	    		elseif($filter[0]=='gt'){
	    			$this->addWhere($filter[1].'>'."{$filter[2]}");
	    		}
    		}
    	}

    	return $this;
    }

    public function addWhere($query){
    	if($this->whereQuery==''){
    		$this->whereQuery = " HAVING {$query}";
    	}
    	else
    	{
    		$this->whereQuery .= " AND {$query}";
    	}
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['installed_on', 'purchased_on', 'expired_on', 'email', 'shop_url', 'purchase_status', 'install_status', 'uninstall_date'], 'safe'],
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
    	
        $dataProvider = '';
        $this->load($params);
        $this->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'shop_url', $this->shop_url]);

        $this->andFilterWhere([
            'id' => $this->id,
            'merchant_id' => $this->merchant_id,
            'installed_on' => $this->installed_on,
            'purchased_on' => $this->purchased_on,
            'expired_on' => $this->expired_on,
            'install_status' => $this->install_status,
            'purchase_status' => $this->purchase_status,
        ]);
        $this->customQuery .= $this->whereQuery;
        // echo $this->customQuery;die;
        if($this->customQuery!=''){
            $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$this->customQuery}) FINAL")->queryScalar();
            $dataProvider = new SqlDataProvider([
                'sql' => $this->customQuery,
                'totalCount' => $totalCount,
                'sort' =>false,
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        }else{
            $query = JetShopDetails::find();
        }
        return $dataProvider;
    }
}