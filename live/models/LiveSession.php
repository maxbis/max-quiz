<?php

namespace app\live\models;

use yii\db\ActiveRecord;

class LiveSession extends ActiveRecord
{
    public const STATUS_LOBBY = 'lobby';
    public const STATUS_QUESTION_OPEN = 'question_open';
    public const STATUS_LEADERBOARD = 'leaderboard';
    public const STATUS_FINISHED = 'finished';

    public static function tableName()
    {
        return 'live_session';
    }

    public function rules()
    {
        return [
            [['quiz_id', 'join_code', 'status'], 'required'],
            [['quiz_id', 'current_question_index', 'question_count', 'created_by_user_id'], 'integer'],
            [['started_at', 'ended_at', 'created_at', 'updated_at'], 'safe'],
            [['join_code'], 'string', 'max' => 16],
            [['status'], 'string', 'max' => 32],
            [['join_code'], 'unique'],
            [['status'], 'in', 'range' => [
                self::STATUS_LOBBY,
                self::STATUS_QUESTION_OPEN,
                self::STATUS_LEADERBOARD,
                self::STATUS_FINISHED,
            ]],
        ];
    }

    public function getQuiz()
    {
        return $this->hasOne(\app\models\Quiz::class, ['id' => 'quiz_id']);
    }

    public function getSessionQuestions()
    {
        return $this->hasMany(LiveSessionQuestion::class, ['live_session_id' => 'id'])
            ->orderBy(['question_order' => SORT_ASC]);
    }
}
