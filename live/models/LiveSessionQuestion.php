<?php

namespace app\live\models;

use yii\db\ActiveRecord;

class LiveSessionQuestion extends ActiveRecord
{
    public static function tableName()
    {
        return 'live_session_question';
    }

    public function rules()
    {
        return [
            [['live_session_id', 'question_id', 'question_order'], 'required'],
            [['live_session_id', 'question_id', 'question_order'], 'integer'],
            [['opened_at', 'closed_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getQuestion()
    {
        return $this->hasOne(\app\models\Question::class, ['id' => 'question_id']);
    }
}
