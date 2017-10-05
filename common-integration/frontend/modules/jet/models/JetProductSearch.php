<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetProduct;
use frontend\modules\jet\components\Dashboard\Productinfo;
/**
 * JetProductSearch represents the model behind the search form about `app\models\JetProduct`.
 */
class JetProductSearch extends JetProduct
{
    /**
     * @inheritdoc
     */

    public $updated_at2, $update_title, $update_price, $option_status,$sku,$option_sku;
    public function rules()
    {
        return [
            [['id', 'qty'], 'integer'],
            [['merchant_id', 'title', 'sku','product_type','type', 'description', /*'image',*/ 'attr_ids', 'jet_attributes', 'upc', 'ASIN','vendor','fulfillment_node','status', 'updated_at2', 'updated_at', 'update_title', 'update_price', 'option_status', 'option_reprice_enable', 'option_buybox_status', 'price_from', 'price_to'], 'safe'],
            [['price'], 'number'],
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

    public function getClassName()
    {
        // bypass scenarios() implementation in the parent class
        return 'JetProductSearch';
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
       // $query = JetProduct::find();
          $merchant_id=Yii::$app->user->identity->id;
        $pageSize = isset($params['per-page']) ? intval($params['per-page']) : 50;

        //$query = JetProduct::find()->select('jet_product.*,update_title,update_price')->joinWith('jetProductDetails')->where(['jet_product_details.merchant_id' => $merchant_id])->andWhere(['!=','fulfillment_node','']);        
        $query = JetProduct::find()->select(['`jet_product`.*, update_title, update_price, IF(jet_product.type="simple",0,count(`jet_product_variants`.`product_id`)) as `option_variants_count`,IF(jet_product.type="simple",null,GROUP_CONCAT(`jet_product_variants`.`status`)) as `option_status`, GROUP_CONCAT(`jet_repricing`.`enable`) as `option_reprice_enable`, GROUP_CONCAT(`jet_repricing`.`buybox_status`) as `option_buybox_status`'])->where(['jet_product.merchant_id' => MERCHANT_ID])->andWhere(['!=','fulfillment_node','']);
        
        $subQuery = (new \yii\db\Query())->select('jet_product_details.*')->from('jet_product_details')->where('merchant_id=' . MERCHANT_ID);
        $query->leftJoin(['jet_product_details' => $subQuery], 'jet_product.merchant_id = jet_product_details.merchant_id AND jet_product.id = jet_product_details.product_id');


        $subQuery = (new \yii\db\Query())->select('option_id, product_id,option_sku,option_unique_id,status,asin')->from('jet_product_variants')->where('merchant_id=' . MERCHANT_ID);
        $query->leftJoin(['jet_product_variants' => $subQuery], 'jet_product_variants.product_id = jet_product.id');

        $subQuery = (new \yii\db\Query())->select('product_id,variant_id, enable, buybox_status, marketplace_price')->from('jet_repricing')->where('merchant_id=' . MERCHANT_ID);
        $query->leftJoin(['jet_repricing' => $subQuery], 'jet_product.id = jet_repricing.product_id AND ((jet_product.type="variants" AND jet_product_variants.option_id=jet_repricing.variant_id) OR (jet_product.type="simple" AND jet_product.variant_id=jet_repricing.variant_id))');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['updated_at'=>SORT_DESC]],
            'pagination' =>  ['pageSize' => $pageSize],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if ($this->status != '') {        	
            //$query->joinWith(['walmart_product_variants']);
            /*$query->andFilterWhere(['like', 'walmart_product.status', $this->status])
                  ->orFilterWhere(['like', 'walmart_product_variants.status', $this->status]);*/
            //$query->andWhere("(walmart_product.status LIKE '".$this->status."') OR (walmart_product_variants.status LIKE '".$this->status."')");

            $query->andWhere("(jet_product.status LIKE '". $this->status ."') OR (jet_product_variants.status LIKE '".$this->status."')");
        }
        if($this->title != ''){
                $query->andWhere("(jet_product_details.update_title LIKE '%" .addslashes( $this->title ). "%') OR (jet_product_details.update_title IS NULL AND jet_product.title LIKE '%" . addslashes( $this->title ) . "%')");
        }
        if ($this->sku != '') {
        	$this->sku = trim($this->sku);
        	$query->andWhere("(jet_product.sku LIKE '" . $this->sku . "') OR (jet_product_variants.option_sku LIKE '" . $this->sku . "')");
        }
        if ($this->upc != '')
        {
        	 
        	$query->andWhere("(jet_product.upc LIKE '" . $this->upc . "') OR (jet_product_variants.option_unique_id LIKE '" . $this->upc . "')");
        }
        if ($this->ASIN != '')
        {        
        	$query->andWhere("(jet_product.ASIN LIKE '" . $this->ASIN . "') OR (jet_product_variants.asin LIKE '" . $this->ASIN . "')");
        }
        if($this->price_from != '' and $this->price_to != ''){
                //if($this->price_from<$this->price_to){
                    $query->andWhere("(jet_product_details.update_price between '" . $this->price_from . "' and '".$this->price_to."') OR (jet_product_details.update_price IS NULL AND jet_product.price between '" . $this->price_from . "' and '".$this->price_to."')");
               /* }else{
                    $query->andWhere("(jet_product_details.update_price between '" . $this->price_to . "' and '".$this->price_from."') OR (jet_product_details.update_price = 0 AND jet_product.price between '" . $this->price_to . "' and '".$this->price_from."')");
                }*/
        }elseif($this->price_to != ''){
            $query->andWhere("(jet_product_details.update_price <= '" . $this->price_to . "') OR (jet_product_details.update_price IS NULL AND jet_product.price <= '" . $this->price_to . "')");
        }elseif($this->price_from != ''){
            $query->andWhere("(jet_product_details.update_price >= '" . $this->price_from . "') OR (jet_product_details.update_price IS NULL AND jet_product.price >= '" . $this->price_from . "')");
        }
        if($this->option_reprice_enable === '0' || $this->option_reprice_enable == 1){
            $query->andWhere("jet_repricing.enable = '" . $this->option_reprice_enable."' AND jet_repricing.marketplace_price != ''" );
        }
        
        if($this->option_buybox_status === '0' || $this->option_buybox_status == 1){
            $query->andWhere("jet_repricing.buybox_status = '" . $this->option_buybox_status ."' AND jet_repricing.marketplace_price != ''");
        }
        if (isset($_GET['low'])){
            $query->andFilterWhere(['<', 'qty', trim($_GET['low'])]);
        }else{
            $query->andFilterWhere(['qty' => $this->qty]);
        }
        if(isset($_GET['updated']) && $_GET['updated']>0){
            $query->andFilterWhere(['between', 'jet_product.updated_at', date('Y-m-d 00:00:00'), date('Y_m-d 23:59:59')]);
        }
        if(isset($_GET['tmp'])){
              $tmpProductIds = [];
              $tmpProductIds = Productinfo::getTempProductsCount($merchant_id, true);
              $query->andFilterWhere(['in', 'jet_product.id', $tmpProductIds]);
              //var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);die();
        }else{
            $query->andFilterWhere([
                'jet_product.id' => $this->id,
               // 'price'=> $this->price,
                'qty' => $this->qty,
            ]);
        }
        $query->andFilterWhere(['like', 'jet_product.merchant_id', $this->merchant_id])
            ->andFilterWhere(['like', 'vendor', $this->vendor])
            //->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'product_type', $this->product_type])           
            //->andFilterWhere(['like', 'upc', $this->upc])
            //->andFilterWhere(['like', 'ASIN', $this->ASIN])
            ->andFilterWhere(['type'=>$this->type])
            ->andFilterWhere(['between', 'updated_at', $this->updated_at, $this->updated_at2]);
            //->andFilterWhere(['status'=>$this->status]);
   //       echo "<hr><pre>";
          $query->groupBy(['jet_product.id']);
//         print_r($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);die();
          //echo "<pre>";print_r($dataProvider->getModels());echo "</pre>";die();
        return $dataProvider;
    }
}