<?php

use app\models\Quiz;
use yii\db\Migration;

class m260522_203247_add_quiz_group_and_language_to_quiz extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%quiz}}', 'quiz_group', $this->string(40)->null()->after('name'));
        $this->addColumn('{{%quiz}}', 'language', $this->string(10)->null()->after('quiz_group'));

        $rows = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('{{%quiz}}')
            ->all($this->db);

        foreach ($rows as $row) {
            $this->update('{{%quiz}}', [
                'quiz_group' => Quiz::deriveQuizGroup((string)$row['name']),
            ], ['id' => $row['id']]);
        }

        $this->alterColumn('{{%quiz}}', 'quiz_group', $this->string(40)->notNull());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%quiz}}', 'language');
        $this->dropColumn('{{%quiz}}', 'quiz_group');
    }
}
