<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log".
 *
 * @property int $id
 * @property int $submission_id
 * @property int|null $quiz_id
 * @property int $question_id
 * @property int $answer_no
 * @property int|null $correct
 * @property string $timestamp
 */
class Log extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['submission_id', 'question_id', 'answer_no'], 'required'],
            [['submission_id', 'quiz_id', 'question_id', 'answer_no', 'correct'], 'integer'],
            [['timestamp'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'submission_id' => 'Submission ID',
            'quiz_id' => 'Quiz ID',
            'question_id' => 'Question ID',
            'answer_no' => 'Answer No',
            'correct' => 'Correct',
            'timestamp' => 'Timestamp',
        ];
    }
}
