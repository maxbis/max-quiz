<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "quiz".
 *
 * @property int $id
 * @property string $name
 * @property string $password
 * @property int $active
 * @property int|null $no_questions
 */
class Quiz extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quiz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'password', 'active'], 'required'],
            [['active', 'no_questions', 'review', 'blind'], 'integer'],
            [['name'], 'string', 'max' => 40],
            [['password'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'password' => 'Code',
            'active' => 'Active',
            'no_questions' => 'No Questions',
            'review' => 'Review',
            'blind' => 'Blind'
        ];
    }
}
