<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetErrorfileInfo;

/**
 * JetErrorfileInfoSearch represents the model behind the search form about `app\models\JetErrorfileInfo`.
 */
class JetErrorfileInfoSearch extends JetErrorfileInfo
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'jetinfofile_id'], 'integer'],
            [['jet_file_id', 'file_name','product_skus', 'file_type', 'status', 'error', 'date'], 'safe'],
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
       // $query = JetErrorfileInfo::find();
       
    	$merchant_id=\Yii::$app->user->identity->id;
    	$query = JetErrorfileInfo::find()->where(['merchant_id' => $merchant_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'date' => $this->date,
            'jetinfofile_id' => $this->jetinfofile_id,
        ]);

        $query->andFilterWhere(['like', 'jet_file_id', $this->jet_file_id])
            ->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'file_type', $this->file_type])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'error', $this->error])
        	->andFilterWhere(['like', 'product_skus', $this->product_skus]);

        return $dataProvider;
    }
}
