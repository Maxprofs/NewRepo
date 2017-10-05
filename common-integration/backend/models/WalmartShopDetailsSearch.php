<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\WalmartShopDetails;
use backend\models\WalmartExtensionDetails;

/**
 * WalmartShopDetailsSearch represents the model behind the search form about `backend\models\WalmartShopDetails`.
 */
class WalmartShopDetailsSearch extends WalmartShopDetails
{
    /**
     * @inheritdoc
     */
    public $install_date;
    public $install_date2;
    public $expire_date;
    public $expire_date2;
    public $status1;
    public $walmartShopDetails;
    public $merchant_id;
    public $merchant_id2;
    public $uninstall_date;
    public $uninstall_date2;

    public function rules()
    {
        return [
            [['id', 'merchant_id','status'], 'integer'],
            [['shop_url','shop_name', 'email', 'token', 'currency','walmartShopDetails','install_date','status1','install_date2','expire_date','expire_date2','uninstall_date','uninstall_date2','seller_username','seller_password'], 'safe'],
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
        $query = WalmartShopDetails::find();

        /*$dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);*/
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' =>['attributes'=>['install_date','expire_date','merchant_id','shop_url','shop_name','email']]
        ]);
         /*$dataProvider->sort->attributes['walmartExtensionDetail'] = [
        'asc' => ['walmart_extension_detail.status' => SORT_ASC],
        'desc' => ['walmart_extension_detail.status' => SORT_DESC],
        'asc' => ['walmart_extension_detail.install_date' => SORT_ASC],
        'desc' => ['walmart_extension_detail.install_date' => SORT_DESC],
        'asc' => ['walmart_extension_detail.expire_date' => SORT_ASC],
        'desc' => ['walmart_extension_detail.expire_date' => SORT_DESC],
    ];*/


        $this->load($params);

        if (!$this->validate()) {
             $dataProvider = new ActiveDataProvider;
            return $dataProvider;
        }
        $query->joinWith(['walmartExtensionDetail']);
        $query->andFilterWhere([
            'walmart_shop_details.merchant_id' => $this->merchant_id,
            'walmart_shop_details.status'=>$this->status,
            'walmart_extension_detail.status'=> $this->status1,

        ]);

        $query->andFilterWhere(['like', 'shop_url', $this->shop_url])
/*            ->andFilterWhere(['like', 'merchant_id', $this->merchant_id2])*/
            ->andFilterWhere(['like', 'shop_name', $this->shop_name])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'seller_username', $this->seller_username])
            ->andFilterWhere(['like', 'seller_password', $this->seller_password])
            ->andFilterWhere(['>=','walmart_extension_detail.install_date',$this->install_date])
            ->andFilterWhere(['<=','walmart_extension_detail.install_date',$this->install_date2])
            ->andFilterWhere(['>=','walmart_extension_detail.expire_date',$this->expire_date])
            ->andFilterWhere(['<=','walmart_extension_detail.expire_date',$this->expire_date2])
            ->andFilterWhere(['<=','walmart_extension_detail.uninstall_date',$this->uninstall_date])
            ->andFilterWhere(['<=','walmart_extension_detail.uninstall_date',$this->uninstall_date2]);
//        var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
//        print_r($dataProvider->getModels());
//        die;

        return $dataProvider;
    }
}
