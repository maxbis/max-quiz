<?php

namespace app\live\models;

use yii\db\ActiveRecord;

class LiveSessionRankSnapshot extends ActiveRecord
{
    public static function tableName()
    {
        return 'live_session_rank_snapshot';
    }

    public function rules()
    {
        return [
            [['live_session_id', 'live_session_question_id', 'submission_id', 'question_order', 'rank_position', 'score'], 'required'],
            [['live_session_id', 'live_session_question_id', 'submission_id', 'question_order', 'rank_position', 'score', 'previous_rank', 'rank_delta'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }
}
