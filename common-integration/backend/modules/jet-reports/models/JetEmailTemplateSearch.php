<?php

namespace backend\modules\reports\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\reports\models\JetEmailTemplate;

/**
 * JetEmailTemplateSearch represents the model behind the search form about `backend\models\JetEmailTemplate`.
 */
class JetEmailTemplateSearch extends JetEmailTemplate
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
        $query = JetEmailTemplate::find();

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
            'show_on_admin_setting' => $this->show_on_admin_setting,
        ]);

        $query->andFilterWhere(['like', 'template_title', $this->template_title])
            ->andFilterWhere(['like', 'template_path', $this->template_path])
            ->andFilterWhere(['like', 'custom_title', $this->custom_title]);

        return $dataProvider;
    }
}
