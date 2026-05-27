<?php

use yii\db\Migration;

class m260527_120000_add_live_quiz_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%live_session}}', [
            'id' => $this->primaryKey(),
            'quiz_id' => $this->integer()->notNull(),
            'join_code' => $this->string(16)->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('lobby'),
            'current_question_index' => $this->integer()->notNull()->defaultValue(0),
            'question_count' => $this->integer()->notNull()->defaultValue(0),
            'created_by_user_id' => $this->integer()->null(),
            'started_at' => $this->dateTime()->null(),
            'ended_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx_live_session_quiz', '{{%live_session}}', 'quiz_id');
        $this->createIndex('ux_live_session_join_code', '{{%live_session}}', 'join_code', true);

        $this->createTable('{{%live_session_question}}', [
            'id' => $this->primaryKey(),
            'live_session_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
            'question_order' => $this->integer()->notNull(),
            'opened_at' => $this->dateTime()->null(),
            'closed_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('ux_live_session_question_order', '{{%live_session_question}}', ['live_session_id', 'question_order'], true);
        $this->createIndex('idx_live_session_question_question', '{{%live_session_question}}', 'question_id');

        $this->createTable('{{%live_session_submission}}', [
            'id' => $this->primaryKey(),
            'live_session_id' => $this->integer()->notNull(),
            'submission_id' => $this->integer()->notNull(),
            'joined_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('ux_live_session_submission_link', '{{%live_session_submission}}', ['live_session_id', 'submission_id'], true);

        $this->createTable('{{%live_session_rank_snapshot}}', [
            'id' => $this->primaryKey(),
            'live_session_id' => $this->integer()->notNull(),
            'live_session_question_id' => $this->integer()->notNull(),
            'submission_id' => $this->integer()->notNull(),
            'question_order' => $this->integer()->notNull(),
            'rank_position' => $this->integer()->notNull(),
            'score' => $this->integer()->notNull()->defaultValue(0),
            'previous_rank' => $this->integer()->null(),
            'rank_delta' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex('idx_live_snapshot_lookup', '{{%live_session_rank_snapshot}}', ['live_session_id', 'question_order']);
        $this->createIndex('ux_live_snapshot_entry', '{{%live_session_rank_snapshot}}', ['live_session_question_id', 'submission_id'], true);

        $this->addColumn('{{%log}}', 'live_session_id', $this->integer()->null()->after('no_answered'));
        $this->addColumn('{{%log}}', 'live_session_question_id', $this->integer()->null()->after('live_session_id'));
        $this->createIndex('idx_log_live_session', '{{%log}}', 'live_session_id');
        $this->createIndex('idx_log_live_session_question', '{{%log}}', 'live_session_question_id');
        $this->createIndex('ux_log_live_answer', '{{%log}}', ['live_session_question_id', 'submission_id'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('ux_log_live_answer', '{{%log}}');
        $this->dropIndex('idx_log_live_session_question', '{{%log}}');
        $this->dropIndex('idx_log_live_session', '{{%log}}');
        $this->dropColumn('{{%log}}', 'live_session_question_id');
        $this->dropColumn('{{%log}}', 'live_session_id');

        $this->dropTable('{{%live_session_rank_snapshot}}');
        $this->dropTable('{{%live_session_submission}}');
        $this->dropTable('{{%live_session_question}}');
        $this->dropTable('{{%live_session}}');
    }
}
