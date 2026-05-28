<?php

namespace app\live\models;

use yii\db\ActiveRecord;

class LiveSession extends ActiveRecord
{
    public const STATUS_LOBBY = 'lobby';
    public const STATUS_QUESTION_OPEN = 'question_open';
    public const STATUS_LEADERBOARD = 'leaderboard';
    public const STATUS_FINISHED = 'finished';
    public const SCORING_MODE_CORRECT_ONLY = 'correct_only';
    public const SCORING_MODE_CORRECT_DIFFICULTY_BONUS = 'correct_difficulty_bonus';

    public static function tableName()
    {
        return 'live_session';
    }

    public function rules()
    {
        return [
            [['quiz_id', 'join_code', 'status', 'scoring_mode'], 'required'],
            [['quiz_id', 'current_question_index', 'question_count', 'created_by_user_id'], 'integer'],
            [['started_at', 'ended_at', 'created_at', 'updated_at'], 'safe'],
            [['join_code'], 'string', 'max' => 16],
            [['status', 'scoring_mode'], 'string', 'max' => 32],
            [['join_code'], 'unique'],
            [['status'], 'in', 'range' => [
                self::STATUS_LOBBY,
                self::STATUS_QUESTION_OPEN,
                self::STATUS_LEADERBOARD,
                self::STATUS_FINISHED,
            ]],
            [['scoring_mode'], 'in', 'range' => [
                self::SCORING_MODE_CORRECT_ONLY,
                self::SCORING_MODE_CORRECT_DIFFICULTY_BONUS,
            ]],
        ];
    }

    public static function scoringModeOptions(): array
    {
        return [
            self::SCORING_MODE_CORRECT_DIFFICULTY_BONUS => 'Correct + difficulty bonus',
            self::SCORING_MODE_CORRECT_ONLY => 'Correct only',
        ];
    }

    public function getScoringModeLabel(): string
    {
        return self::scoringModeOptions()[$this->scoring_mode] ?? ucfirst(str_replace('_', ' ', (string)$this->scoring_mode));
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
