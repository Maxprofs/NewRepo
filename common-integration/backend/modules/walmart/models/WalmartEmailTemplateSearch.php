<?php

namespace backend\modules\walmart\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\walmart\models\WalmartEmailTemplate;

/**
 * WalmartEmailTemplateSearch represents the model behind the search form about `backend\modules\reports\models\WalmartEmailTemplate`.
 */
class WalmartEmailTemplateSearch extends WalmartEmailTemplate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'show_on_admin_setting'], 'integer'],
            [['template_title', 'template_path', 'custom_title'], 'safe'],
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
        $query = WalmartEmailTemplate::find();

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
            'show_on_admin_setting' => $this->show_on_admin_setting,
        ]);

        $query->andFilterWhere(['like', 'template_title', $this->template_title])
            ->andFilterWhere(['like', 'template_path', $this->template_path])
            ->andFilterWhere(['like', 'custom_title', $this->custom_title]);

        return $dataProvider;
    }
}
