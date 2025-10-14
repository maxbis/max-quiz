<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "quizquestion".
 *
 * @property int $id
 * @property int $quiz_id
 * @property int $question_id
 * @property int|null $order
 */
class Quizquestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quizquestion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quiz_id', 'question_id'], 'required'],
            [['quiz_id', 'question_id', 'order'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quiz_id' => 'Quiz ID',
            'question_id' => 'Question ID',
            'order' => 'Order',
        ];
    }
}
