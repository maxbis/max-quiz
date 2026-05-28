<?php

use yii\db\Migration;

class m260528_120000_add_scoring_mode_to_live_session extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%live_session}}', 'scoring_mode', $this->string(32)->notNull()->defaultValue('correct_difficulty_bonus')->after('status'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%live_session}}', 'scoring_mode');
    }
}
