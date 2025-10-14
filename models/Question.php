<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Question".
 *
 * @property int $id
 * @property string $question
 * @property string $a1
 * @property string $a2
 * @property string|null $a3
 * @property string|null $a4
 * @property string|null $a5
 * @property string|null $a6
 * @property int $correct
 * @property string|null $label
 * @property int|null $order Virtual property for quizquestion.order
 */
class Question extends \yii\db\ActiveRecord
{
    public $order; // Virtual property for quizquestion.order
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['question', 'a1', 'a2', 'correct'], 'required'],
            [['correct', 'order'], 'integer'],
            [['question'], 'string', 'max' => 800],
            [['a1', 'a2', 'a3', 'a4', 'a5', 'a6'], 'string', 'max' => 300],
            [['label'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Question ID',
            'question' => 'Question',
            'a1' => 'Answer #1',
            'a2' => 'Answer #2',
            'a3' => 'Answer #3',
            'a4' => 'Answer #4',
            'a5' => 'Answer #5',
            'a6' => 'Answer #6',
            'correct' => 'Correct Answer',
            'label' => 'Label',
            'order' => 'Order',
        ];
    }

    public function getQuizquestion()
    {
        return $this->hasOne(Quizquestion::className(), ['question_id' => 'id']);
    }
    public function getQuizquestions()
    {
        return $this->hasMany(Quizquestion::className(), ['question_id' => 'id'])
                    ->where(['quizquestion.active' => 1]);
    }
}
