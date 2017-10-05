<?php

namespace backend\modules\walmart\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\walmart\models\JetExtensionDetail;
use yii\data\SqlDataProvider;
use backend\modules\walmart\components\Data;

/**
 * JetExtensionDetailSearch represents the model behind the search form about `backend\modules\reports\models\JetExtensionDetail`.
 */
class EmailReportSearch extends JetExtensionDetail
{
    
    public $customQuery = '';
    public $whereQuery = '';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Data::EMAIL_REPORT;
    }
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
            [[ 'mail_status','send_at','read_at'], 'safe'],
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
        $this->andFilterWhere(['like', 'mail_status', $this->mail_status])
            ->andFilterWhere(['like', 'send_at', $this->send_at])
            ->andFilterWhere(['like', 'read_at', $this->read_at]);

        $this->andFilterWhere([
            'tracking_id' => $this->tracking_id,
            'merchant_id' => $this->merchant_id,
        ]);
        $this->customQuery .= $this->whereQuery;
        if($this->customQuery!=''){
            $totalCount = Yii::$app->db->createCommand("SELECT COUNT(*) FROM ({$this->customQuery}) FINAL")->queryScalar();
            $dataProvider = new SqlDataProvider([
                'sql' => $this->customQuery,
                'totalCount' => $totalCount,
                'sort'=>  [
                    'attributes' => [
                        'tracking_id'=>SORT_ASC,
                    ],
                ],
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        }else{
            $query = JetExtensionDetail::find();
        }

        /*

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        

        
*/
        //print_r($dataProvider);die;
        return $dataProvider;
    }
    
}
