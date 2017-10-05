<?php

namespace frontend\modules\neweggmarketplace\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\JetProduct;
use frontend\modules\neweggmarketplace\models\NeweggProduct;

/**
 * NeweggProductSearch represents the model behind the search form about `frontend\modules\neweggmarketplace\models\NeweggProduct`.
 */
class NeweggProductSearch extends NeweggProduct
{
    /**
     * @inheritdoc
     */
    public $title;
    public $sku;
    public $qty;
    public $price;
    public $vendor;
    public $upc;
    public $upload_status;
    public $type;
    public $product_type;
    public function rules()
    {

        return [
            [['id', 'product_id', 'merchant_id'], 'integer'],
            [['type', 'error', 'title','sku','qty','price','vendor','upc','shopify_product_type','upload_status','price_from', 'price_to'], 'safe'],
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
        $query = NeweggProduct::find()->select(['`newegg_product`.*, count(DISTINCT `newegg_product_variants`.`option_id`) as `option_variants_count`, GROUP_CONCAT(`newegg_product_variants`.`upload_status`) as `option_status`'])->where(['newegg_product.merchant_id' => MERCHANT_ID])->andWhere(['!=','newegg_product.newegg_category',''])->andWhere(['<>','newegg_product.upload_status', 'DELETED']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' =>  ['pageSize' => $pageSize],
            'sort' => ['attributes' => ['product_id', 'title', 'sku', 'qty', 'price', 'product_type', 'upc', 'type', 'upload_status','vendor']]

        ]);

        $this->load($params);

        /*if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }*/
        $subQuery = (new \yii\db\Query())->select('id,product_type,title,sku,qty,price,upc,updated_at,type,vendor')->from('jet_product')->where('merchant_id=' . MERCHANT_ID);
        $query->innerJoin(['jet_product' => $subQuery], 'jet_product.id = newegg_product.product_id');
        /*$query->joinWith ( [
            'jet_product'
        ] );*/

        $query->andFilterWhere([
            'id' => $this->id,
            'newegg_product.product_id' => $this->product_id,
        ]);
        $query->andFilterWhere([
            'id' => $this->id,
//            'product_id' => $this->product_id,
            'merchant_id' => $this->merchant_id,
        ]);
        $subQuery = (new \yii\db\Query())->select('product_id,option_id,upload_status')->from('newegg_product_variants')->where('merchant_id=' . MERCHANT_ID);
        $query->leftJoin(['newegg_product_variants' => $subQuery], 'newegg_product_variants.product_id = newegg_product.product_id');

        $subQuery1 = (new \yii\db\Query())->select('option_id,product_id,option_sku,option_unique_id')->from('jet_product_variants')->where('merchant_id=' . MERCHANT_ID);
        $query->leftJoin(['jet_product_variants' => $subQuery1], 'jet_product_variants.option_id = newegg_product_variants.option_id');

        if ($this->sku != '') {
            //by shivam


            $query->andWhere("(jet_product.sku LIKE '" . $this->sku . "') OR (jet_product_variants.option_sku LIKE '" . $this->sku . "')");

            //end by shivam
        }
        if ($this->upload_status == 'other') {
            $allStatus = ['ACTIVATED', 'SUBMITTED', 'DEACTIVATED', 'Not Uploaded', 'UPLOAD WITH ERROR'];

            $query->andWhere("(newegg_product.upload_status NOT IN ('" . implode("','", $allStatus) . "') OR (jet_product.type='simple' AND newegg_product.upload_status IS NULL)) OR (newegg_product_variants.upload_status NOT IN ('" . implode("','", $allStatus) . "') OR (jet_product.type='variants' AND newegg_product_variants.upload_status IS NULL))");
        } elseif ($this->upload_status != '') {

            $query->andWhere("(newegg_product.upload_status LIKE '" . $this->upload_status . "') OR (newegg_product_variants.upload_status LIKE '" . $this->upload_status . "')");
        }
        if ($this->upc != '') {
            //by shivam

            $query->andWhere("(jet_product.upc LIKE '" . $this->upc . "') OR (jet_product_variants.option_unique_id LIKE '" . $this->upc . "')");

            //end by shivam
        }
        if ($this->price_from != '' and $this->price_to != '') {
            $query->andWhere("(newegg_product.product_price between '" . $this->price_from . "' and '" . $this->price_to . "') OR (newegg_product.product_price IS NULL AND jet_product.price between '" . $this->price_from . "' and '" . $this->price_to . "')");
        } elseif ($this->price_to != '') {
            $query->andWhere("(newegg_product.product_price <= '" . $this->price_to . "') OR (newegg_product.product_price IS NULL AND jet_product.price <= '" . $this->price_to . "')");
        } elseif ($this->price_from != '') {
            $query->andWhere("(newegg_product.product_price >= '" . $this->price_from . "') OR (newegg_product.product_price IS NULL AND jet_product.price >= '" . $this->price_from . "')");
        }

        /*$dataProvider->sort->attributes['jet_product'] = [
            'asc' => ['jet_product.title' => SORT_ASC],
            'desc' => ['jet_product.title' => SORT_DESC],
        ];*/
        $query->andFilterWhere(['like', 'category', $this->newegg_category])
            //->andFilterWhere(['=', 'newegg_product.upload_status', $this->upload_status])
            ->andFilterWhere(['like', 'error', $this->error])
            ->andFilterWhere(['like', 'newegg_product.shopify_product_type', $this->shopify_product_type]);
        $query->andWhere('product_title LIKE "%' . addslashes($this->title) . '%" ' . ' OR ( product_title IS NULL AND jet_product.title LIKE "%' . addslashes($this->title) . '%")');

//        $query->andFilterWhere(['like', 'jet_product.title', $this->title])
//        $query->andFilterWhere(['like', 'jet_product.sku', $this->sku])
        $query->andFilterWhere(['like', 'jet_product.qty', $this->qty])
//            ->andFilterWhere(['like', 'jet_product.price', $this->price])
//            ->andFilterWhere(['like', 'jet_product.upc', $this->upc])
            ->andFilterWhere(['like', 'jet_product.type', $this->type])
            ->andFilterWhere(['like', 'jet_product.vendor', $this->vendor]);

        $query->groupBy(['newegg_product.product_id']);

//        var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);die();

        return $dataProvider;
    }

}
