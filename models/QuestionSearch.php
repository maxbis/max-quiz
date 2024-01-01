<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Question;

/**
 * QuestionSearch represents the model behind the search form of `app\models\Question`.
 */
class QuestionSearch extends Question
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'correct'], 'integer'],
            [['question', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'label'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Question::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC, // Sort by id in descending order
                ],
            ],
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
            'correct' => $this->correct,
        ]);

        $query->andFilterWhere(['like', 'question', $this->question])
            ->andFilterWhere(['like', 'a1', $this->a1])
            ->andFilterWhere(['like', 'a2', $this->a2])
            ->andFilterWhere(['like', 'a3', $this->a3])
            ->andFilterWhere(['like', 'a4', $this->a4])
            ->andFilterWhere(['like', 'a5', $this->a5])
            ->andFilterWhere(['like', 'a6', $this->a6])
            ->andFilterWhere(['like', 'label', $this->label]);

        return $dataProvider;
    }
}
