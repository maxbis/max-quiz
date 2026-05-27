<?php

namespace app\live\models;

use yii\db\ActiveRecord;

class LiveSessionSubmission extends ActiveRecord
{
    public static function tableName()
    {
        return 'live_session_submission';
    }

    public function rules()
    {
        return [
            [['live_session_id', 'submission_id'], 'required'],
            [['live_session_id', 'submission_id'], 'integer'],
            [['joined_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getSubmission()
    {
        return $this->hasOne(\app\models\Submission::class, ['id' => 'submission_id']);
    }
}
