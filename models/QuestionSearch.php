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
    public $order; // Virtual attribute for quizquestion.order
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'correct', 'order'], 'integer'],
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
    public function search($params, $quiz_id = null, $active = -1)
    {
        $query = Question::find();

        // Always join with quizquestion to get order field
        $query->select(['question.*', 'quizquestion.order as order']);
        $query->joinWith(['quizquestion']);
        
        if ( $quiz_id ) {
            $query->andWhere(['quizquestion.quiz_id' => $quiz_id]);
            if ( $active == 1 || $active == 0 ) {
                $query->andWhere(['quizquestion.active' => $active]);
            }
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'question',
                    'label',
                    'correct',
                    'order' => [
                        'asc' => ['quizquestion.order' => SORT_ASC],
                        'desc' => ['quizquestion.order' => SORT_DESC],
                        'default' => SORT_ASC,
                    ],
                ],
                'defaultOrder' => [
                    'order' => SORT_ASC,
                    'id' => SORT_ASC
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
            'question.id' => $this->id,
            'correct' => $this->correct,
        ]);

        $query->andFilterWhere(['like', 'question', $this->question])
            ->andFilterWhere(['like', 'label', $this->label]);

        return $dataProvider;
    }
}
