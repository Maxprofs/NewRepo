<?php

namespace frontend\modules\jet\models;

use frontend\modules\jet\models\JetProductFileUpload;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * JetProductFileUploadSearch represents the model behind the search form about `frontend\modules\jet\models\JetProductFileUpload`.
 */
class JetProductFileUploadSearch extends JetProductFileUpload
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'merchant_id', 'total_processed', 'error_count', 'expires_in_seconds'], 'integer'],
            [['local_file_path', 'file_name', 'file_type', 'file_url', 'jet_file_id', 'received', 'processing_start', 'processing_end', 'error_url', 'error_excerpt', 'file_upload_time', 'status'], 'safe'],
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
    	$merchant_id=\Yii::$app->user->identity->id;
        $query = JetProductFileUpload::find()->where(['merchant_id' => $merchant_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        	'sort'=> ['defaultOrder' => ['file_upload_time'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'merchant_id' => $this->merchant_id,
            'total_processed' => $this->total_processed,
            'error_count' => $this->error_count,
            'expires_in_seconds' => $this->expires_in_seconds,
            'file_upload_time' => $this->file_upload_time,
        ]);

        $query->andFilterWhere(['like', 'local_file_path', $this->local_file_path])
            ->andFilterWhere(['like', 'file_name', $this->file_name])
            ->andFilterWhere(['like', 'file_type', $this->file_type])
            ->andFilterWhere(['like', 'file_url', $this->file_url])
            ->andFilterWhere(['like', 'jet_file_id', $this->jet_file_id])
            ->andFilterWhere(['like', 'received', $this->received])
            ->andFilterWhere(['like', 'processing_start', $this->processing_start])
            ->andFilterWhere(['like', 'processing_end', $this->processing_end])
            ->andFilterWhere(['like', 'error_url', $this->error_url])
            ->andFilterWhere(['like', 'error_excerpt', $this->error_excerpt])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
