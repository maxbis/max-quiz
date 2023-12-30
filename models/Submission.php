<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Submission".
 *
 * @property int $id
 * @property int|null $token
 * @property string $first_name
 * @property string $last_name
 * @property string $class
 * @property string $start_time
 * @property string|null $end_time
 * @property string $question_order
 * @property int $no_questions
 * @property int|null $no_answered
 * @property int|null $no_correct
 * @property int $quiz_id
 */
class Submission extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Submission';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'no_questions', 'no_answered', 'no_correct', 'quiz_id'], 'integer'],
            [['first_name', 'last_name', 'class', 'question_order', 'no_questions', 'quiz_id'], 'required'],
            [['start_time', 'end_time'], 'safe'],
            [['first_name', 'last_name'], 'string', 'max' => 40],
            [['class'], 'string', 'max' => 8],
            [['question_order'], 'string', 'max' => 600],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'class' => 'Class',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'question_order' => 'Question Order',
            'no_questions' => 'No Questions',
            'no_answered' => 'No Answered',
            'no_correct' => 'No Correct',
            'quiz_id' => 'Quiz ID',
        ];
    }

    public function getQuiz()
    {
        return $this->hasOne(Quiz::className(), ['id' => 'quiz_id']);
    }
}
