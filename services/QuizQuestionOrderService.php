<?php

namespace app\services;

use Yii;
use yii\db\Query;

class QuizQuestionOrderService
{
    /**
     * @return array<int>
     */
    public function buildQuestionIdsForQuiz(int $quizId, int $mode): array
    {
        $quizQuestionSchema = Yii::$app->db->schema->getTableSchema('quizquestion');
        $questionQuery = (new Query())
            ->select([
                'question_id' => 'qq.question_id',
                'label' => 'q.label',
            ])
            ->from(['qq' => 'quizquestion'])
            ->innerJoin(['q' => 'question'], 'q.id = qq.question_id')
            ->where(['qq.quiz_id' => $quizId, 'qq.active' => 1]);

        if ($quizQuestionSchema !== null && isset($quizQuestionSchema->columns['order'])) {
            $questionQuery->orderBy([
                'qq.order' => SORT_ASC,
                'qq.question_id' => SORT_ASC,
            ]);
        } else {
            $questionQuery->orderBy(['qq.question_id' => SORT_ASC]);
        }

        $rows = $questionQuery->all();

        return $this->orderRows($rows, $mode);
    }

    /**
     * @param array<int, array{question_id:mixed, label:mixed}> $rows
     * @return array<int>
     */
    public function orderRows(array $rows, int $mode, ?callable $shuffleCallback = null): array
    {
        $questionIds = array_map(
            static fn(array $row): int => (int) $row['question_id'],
            $rows
        );

        if ($mode === 1) {
            $this->shuffleArray($questionIds, $shuffleCallback);
            return $questionIds;
        }

        if ($mode !== 2) {
            return $questionIds;
        }

        $groups = [];
        $currentLabel = null;
        $currentGroup = [];

        foreach ($rows as $row) {
            $label = $this->normalizeLabel($row['label'] ?? null);
            $questionId = (int) $row['question_id'];

            if ($currentGroup !== [] && $label !== $currentLabel) {
                $groups[] = $currentGroup;
                $currentGroup = [];
            }

            $currentLabel = $label;
            $currentGroup[] = $questionId;
        }

        if ($currentGroup !== []) {
            $groups[] = $currentGroup;
        }

        if ($groups === []) {
            return [];
        }

        $this->shuffleArray($groups, $shuffleCallback);

        return array_merge(...$groups);
    }

    private function normalizeLabel(mixed $label): string
    {
        return trim((string) $label);
    }

    /**
     * @param array<mixed> $items
     */
    private function shuffleArray(array &$items, ?callable $shuffleCallback = null): void
    {
        if (count($items) < 2) {
            return;
        }

        if ($shuffleCallback !== null) {
            $shuffleCallback($items);
            return;
        }

        shuffle($items);
    }
}
