<?php

namespace backend\modules\reports\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\reports\models\Issues;

/**
 * IssuesSearch represents the model behind the search form about `backend\modules\reports\models\Issues`.
 */
class IssuesSearch extends Issues
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['issue_type', 'issue_description', 'issue_date', 'resolve_date', 'assign','issue_status'], 'safe'],
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
        $query = Issues::find();

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
            'issue_date' => $this->issue_date,
            'resolve_date' => $this->resolve_date,
            'issue_status' => $this->issue_status,
        ]);

        $query->andFilterWhere(['like', 'issue_type', $this->issue_type])
            ->andFilterWhere(['like', 'issue_description', $this->issue_description])
            ->andFilterWhere(['like','issue_status', $this->issue_status])
            ->andFilterWhere(['like', 'assign', $this->assign]);

        return $dataProvider;
    }
}
