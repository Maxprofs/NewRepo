<?php

namespace backend\modules\reports\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\reports\models\JetExtensionDetail;

/**
 * JetExtensionDetailSearch represents the model behind the search form about `backend\modules\reports\models\JetExtensionDetail`.
 */
class JetExtensionDetailSearch extends JetExtensionDetail
{
    public $customWhere = '';
  //  public $attribute ;
    public function setCustomWhere($where){
        $this->customWhere = $where;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id'], 'integer'],
            [['install_date', 'date', 'expire_date', 'email', 'shopurl', 'status', 'app_status', 'uninstall_date'], 'safe'],
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
             $query = JetExtensionDetail::find()->where($this->customWhere);
        }else{
            $query = JetExtensionDetail::find();
        }
        

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
        $query->andFilterWhere([
            'id' => $this->id,
            'merchant_id' => $this->merchant_id,
            'install_date' => $this->install_date,
            'date' => $this->date,
            'expire_date' => $this->expire_date,
            'uninstall_date' => $this->uninstall_date,
            'app_status' => $this->app_status,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'shopurl', $this->shopurl])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
    public function getAttributes(){
      //  $this->attributes('app_status');
      /*  $attr['app_status'] = 'app_status';
        $attr['merchant_id'] = 'merchant_id';
        $attr['shop_url'] = 'shop_url';
        $attr['email'] = 'email';*/
        $filter =[];
        $data = $this->attributes();
        foreach ($data as $key => $value) {
            $filter[$value]= $this->$value;
        }
    //    print_r($filer);die;
       

         return $filter;
    }
}
