<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Submission;

/**
 * SubmissionSearch represents the model behind the search form of `app\models\Submission`.
 */
class SubmissionSearch extends Submission
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'token', 'no_questions', 'no_answered', 'no_correct', 'finished', 'quiz_id'], 'integer'],
            [['first_name', 'last_name', 'class', 'start_time', 'end_time', 'question_order'], 'safe'],
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
    public function search($params, $quiz_id = null)
    {
        $query = Submission::find();

        if ($quiz_id) {
            $query->andWhere(['quiz_id' => $quiz_id]);
        }

        $query->joinWith(['quiz']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 60,
            ],
            'sort' => [

                'attributes' => [
                    'answeredScore' => [
                        'asc' => ['CASE
                        WHEN `submission`.`no_answered` > 0 THEN `submission`.`no_correct` * 100 / `submission`.`no_answered`
                        ELSE 0
                    END' => SORT_ASC
                        ],
                        'desc' => ['CASE
                        WHEN `submission`.`no_answered` > 0 THEN `submission`.`no_correct` * 100 / `submission`.`no_answered`
                        ELSE 0
                    END' => SORT_DESC
                        ],
                        'default' => SORT_DESC,
                    ],
                    'first_name' => [
                        'asc' => ['concat(first_name, last_name)' => SORT_ASC],
                        'desc' => ['concat(first_name, last_name)' => SORT_DESC],
                        'default' => SORT_ASC,
                    ],
                    'last_updated' => [
                        'asc' => ['CASE
                            WHEN `last_updated` IS NULL THEN 0 ELSE `last_updated`
                        END' => SORT_ASC,
                        ],
                        'desc' => ['CASE
                             WHEN `last_updated` IS NULL THEN 0 ELSE `last_updated`
                        END' => SORT_DESC,
                        ],
                    ],
                    'no_answered' => [
                        'asc' => ['no_answered' => SORT_ASC],
                        'desc' => ['no_answered' => SORT_DESC],
                        'default' => SORT_ASC,
                    ],
                ],

                // 'defaultOrder' => [
                //     'last_updated' => SORT_DESC,
                // ],
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
            'token' => $this->token,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'no_questions' => $this->no_questions,
            'no_answered' => $this->no_answered,
            'no_correct' => $this->no_correct,
            'finished' => $this->finished,
            'quiz_id' => $this->quiz_id,
        ]);

        $query->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'class', $this->class])
            ->andFilterWhere(['like', 'question_order', $this->question_order]);

        if ($quiz_id == null) {
            $query->andFilterWhere([
                'quiz.active' => 1,
            ]);
        }


        return $dataProvider;
    }

}
