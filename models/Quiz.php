<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "quiz".
 *
 * @property int $id
 * @property string $name
 * @property string $quiz_group
 * @property string|null $language
 * @property string $password
 * @property int $active
 * @property int|null $no_questions
 * @property int $archived
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
            [['name', 'quiz_group', 'password', 'active'], 'required'],
            [['active', 'no_questions', 'review', 'random', 'blind', 'ip_check', 'archived'], 'integer'],
            [['name'], 'string', 'max' => 40],
            [['quiz_group'], 'string', 'max' => 40],
            [['language'], 'string', 'max' => 10],
            [['password'], 'string', 'max' => 20],
            ['archived', 'default', 'value' => 0],
            [['name', 'quiz_group', 'password', 'language'], 'trim'],
            ['language', 'default', 'value' => null],
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
            'quiz_group' => 'Quiz Group',
            'language' => 'Language',
            'password' => 'Code',
            'active' => 'Active',
            'no_questions' => 'No Questions',
            'review' => 'Review',
            'random' => 'Random',
            'blind' => 'Blind',
            'ip_check' => 'IP Check',
            'archived' => 'Archived',
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if ($this->quiz_group === null || $this->quiz_group === '') {
                $this->quiz_group = static::deriveQuizGroup((string)$this->name);
            }

            if ($this->language !== null) {
                $this->language = strtolower((string)$this->language);
            }

            if ($this->language === '') {
                $this->language = null;
            }

            return true;
        }

        return false;
    }

    public static function deriveQuizGroup(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $parts = explode('.', $name, 2);

        return trim($parts[0]);
    }

    public static function normalizeComparableValue(?string $value): string
    {
        return trim((string)$value);
    }

    public static function buildAggregationKey(?string $quizGroup, ?string $name, ?int $quizId = null): string
    {
        $normalizedGroup = static::normalizeComparableValue($quizGroup);
        $normalizedName = static::normalizeComparableValue($name);

        if ($normalizedGroup === '') {
            return 'quiz:' . (string)$quizId;
        }

        return $normalizedGroup . '||' . $normalizedName;
    }
}
