<?php

namespace frontend\modules\jet\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\jet\models\JetCategory;
use frontend\modules\jet\models\JetSelectedCategory;

/**
 * JetCategorySearch represents the model behind the search form about `app\models\JetCategory`.
 */
class JetCategorySearch extends JetCategory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'merchant_id', 'parent_id', 'root_id', 'level'], 'integer'],
            [['title', 'parent_title', 'root_title', 'jet_attributes'], 'safe'],
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
        $query = JetCategory::find();
       // $query->joinWith('JetSelected');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'merchant_id' => $this->merchant_id,
            'parent_id' => $this->parent_id,
            'root_id' => $this->root_id,
            'level' => $this->level,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'parent_title', $this->parent_title])
            ->andFilterWhere(['like', 'root_title', $this->root_title])
            ->andFilterWhere(['like', 'jet_attributes', $this->jet_attributes]);

        return $dataProvider;
    }


    public static function get_attributes($id){
    	$model = JetSelectedCategory::find()->where(["category_id" => $id])->one();
    	if(!empty($model)){
    		return $model->jet_attributes;
    	}
    	return null;
    }
    public static function get_status($id)
    {
    	if (Yii::$app->user->isGuest) {
    		return \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
    	}
    	$merchant_id= \Yii::$app->user->identity->id;
    	$model = JetSelectedCategory::find()->where(["category_id" => $id,"merchant_id"=>$merchant_id])->one();
    	if($model)
    		return "created";
    	else
    		return "not created";
    }
}
