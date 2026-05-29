<?php

namespace app\live\services;

use app\live\models\LiveSession;
use app\live\models\LiveSessionQuestion;
use app\live\models\LiveSessionRankSnapshot;
use Yii;
use yii\db\Query;

class LiveLeaderboardService
{
    public const TOP_LIMIT = 5;
    public const MOVER_LIMIT = 5;
    private const BASE_CORRECT_POINTS = 10;
    private const FIRST_CORRECT_BONUS_POINTS = 1;

    public function buildLeaderboard(LiveSession $session): array
    {
        if ($session->scoring_mode === LiveSession::SCORING_MODE_CORRECT_DIFFICULTY_BONUS) {
            return $this->buildDifficultyBonusLeaderboard($session);
        }

        $firstCorrectBonuses = $this->getFirstCorrectBonuses((int)$session->id);
        $firstCorrectBonusCounts = $firstCorrectBonuses['counts'];

        $rows = (new Query())
            ->select([
                'submission_id' => 's.id',
                'first_name' => 's.first_name',
                'last_name' => 's.last_name',
                'class' => 's.class',
                'score' => 'COALESCE(SUM(CASE WHEN l.live_session_id = :sessionId THEN l.correct ELSE 0 END), 0)',
                'correct_answers' => 'COALESCE(SUM(CASE WHEN l.live_session_id = :sessionId AND l.correct = 1 THEN 1 ELSE 0 END), 0)',
                'latest_correct_at' => 'MAX(CASE WHEN l.live_session_id = :sessionId AND l.correct = 1 THEN l.timestamp ELSE NULL END)',
            ])
            ->from(['lss' => 'live_session_submission'])
            ->innerJoin(['s' => 'submission'], 's.id = lss.submission_id')
            ->leftJoin(['l' => 'log'], 'l.submission_id = s.id AND l.live_session_id = :sessionId')
            ->where(['lss.live_session_id' => $session->id])
            ->groupBy(['s.id', 's.first_name', 's.last_name', 's.class'])
            ->orderBy([
                'score' => SORT_DESC,
                'correct_answers' => SORT_DESC,
                'latest_correct_at' => SORT_ASC,
                's.id' => SORT_ASC,
            ])
            ->addParams([':sessionId' => $session->id])
            ->all();

        $leaderboard = [];
        $rank = 0;
        foreach ($rows as $row) {
            $rank++;
            $submissionId = (int)$row['submission_id'];
            $leaderboard[] = [
                'rank' => $rank,
                'submission_id' => $submissionId,
                'name' => trim((string)$row['first_name'] . ' ' . (string)$row['last_name']),
                'class' => trim((string)$row['class']),
                'score' => (int)$row['score'] + (($firstCorrectBonusCounts[$submissionId] ?? 0) * self::FIRST_CORRECT_BONUS_POINTS),
                'correct_answers' => (int)$row['correct_answers'],
                'latest_correct_at' => $row['latest_correct_at'],
            ];
        }

        usort($leaderboard, static function (array $left, array $right): int {
            if ($left['score'] !== $right['score']) {
                return $right['score'] <=> $left['score'];
            }
            if ($left['correct_answers'] !== $right['correct_answers']) {
                return $right['correct_answers'] <=> $left['correct_answers'];
            }
            if ($left['latest_correct_at'] !== $right['latest_correct_at']) {
                return ($left['latest_correct_at'] ?? '9999-12-31 23:59:59') <=> ($right['latest_correct_at'] ?? '9999-12-31 23:59:59');
            }
            return $left['submission_id'] <=> $right['submission_id'];
        });

        foreach ($leaderboard as $index => &$entry) {
            $entry['rank'] = $index + 1;
        }
        unset($entry);

        return $leaderboard;
    }

    public function snapshotCurrentLeaderboard(LiveSession $session, LiveSessionQuestion $sessionQuestion): array
    {
        $leaderboard = $this->buildLeaderboard($session);
        $previousRanks = $this->getPreviousRanks($session->id, $sessionQuestion->question_order);

        LiveSessionRankSnapshot::deleteAll([
            'live_session_id' => $session->id,
            'live_session_question_id' => $sessionQuestion->id,
        ]);

        foreach ($leaderboard as $entry) {
            $previousRank = $previousRanks[$entry['submission_id']] ?? null;
            $snapshot = new LiveSessionRankSnapshot([
                'live_session_id' => $session->id,
                'live_session_question_id' => $sessionQuestion->id,
                'submission_id' => $entry['submission_id'],
                'question_order' => $sessionQuestion->question_order,
                'rank_position' => $entry['rank'],
                'score' => $entry['score'],
                'previous_rank' => $previousRank,
                'rank_delta' => $previousRank === null ? 0 : ($previousRank - $entry['rank']),
            ]);
            $snapshot->save(false);
        }

        return $this->buildPresentationData($session, $sessionQuestion, $leaderboard);
    }

    public function buildPresentationData(LiveSession $session, ?LiveSessionQuestion $sessionQuestion = null, ?array $leaderboard = null): array
    {
        $leaderboard = $leaderboard ?? $this->buildLeaderboard($session);
        $questionOrder = $sessionQuestion ? (int)$sessionQuestion->question_order : (int)$session->current_question_index;
        $snapshotRows = [];

        if ($questionOrder > 0) {
            $snapshotRows = LiveSessionRankSnapshot::find()
                ->where([
                    'live_session_id' => $session->id,
                    'question_order' => $questionOrder,
                ])
                ->orderBy(['rank_position' => SORT_ASC])
                ->asArray()
                ->all();
        }

        $snapshotMap = [];
        foreach ($snapshotRows as $row) {
            $snapshotMap[(int)$row['submission_id']] = $row;
        }

        $top = [];
        foreach (array_slice($leaderboard, 0, self::TOP_LIMIT) as $entry) {
            $snapshot = $snapshotMap[$entry['submission_id']] ?? null;
            $entry['rank_delta'] = $snapshot['rank_delta'] ?? 0;
            $entry['previous_rank'] = $snapshot['previous_rank'] ?? null;
            $top[] = $entry;
        }

        $movers = array_values(array_filter(array_map(static function (array $entry) use ($snapshotMap): ?array {
            $snapshot = $snapshotMap[$entry['submission_id']] ?? null;
            $delta = (int)($snapshot['rank_delta'] ?? 0);
            if ($delta <= 0) {
                return null;
            }
            $entry['rank_delta'] = $delta;
            $entry['previous_rank'] = $snapshot['previous_rank'] ?? null;
            return $entry;
        }, $leaderboard)));

        usort($movers, static function (array $left, array $right): int {
            if ($left['rank_delta'] === $right['rank_delta']) {
                return $left['rank'] <=> $right['rank'];
            }
            return $right['rank_delta'] <=> $left['rank_delta'];
        });

        return [
            'top' => $top,
            'movers' => array_slice($movers, 0, self::MOVER_LIMIT),
            'totalPlayers' => count($leaderboard),
            'leaderboard' => $leaderboard,
        ];
    }

    public function calculateDifficultyBonus(int $correctCount, int $totalPlayers): int
    {
        if ($correctCount <= 0 || $totalPlayers <= 0) {
            return 0;
        }

        $ratio = $correctCount / $totalPlayers;
        if ($ratio <= 0.10) {
            return 10;
        }
        if ($ratio <= 0.20) {
            return 5;
        }
        if ($ratio <= 0.30) {
            return 2;
        }
        if ($ratio <= 0.50) {
            return 1;
        }

        return 0;
    }

    private function getPreviousRanks(int $sessionId, int $questionOrder): array
    {
        if ($questionOrder <= 1) {
            return [];
        }

        $previousQuestionOrder = (new Query())
            ->from('live_session_rank_snapshot')
            ->where(['live_session_id' => $sessionId])
            ->andWhere(['<', 'question_order', $questionOrder])
            ->max('question_order');

        if ($previousQuestionOrder === false || $previousQuestionOrder === null) {
            return [];
        }

        $rows = LiveSessionRankSnapshot::find()
            ->select(['submission_id', 'rank_position'])
            ->where([
                'live_session_id' => $sessionId,
                'question_order' => (int)$previousQuestionOrder,
            ])
            ->asArray()
            ->all();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['submission_id']] = (int)$row['rank_position'];
        }

        return $map;
    }

    private function buildDifficultyBonusLeaderboard(LiveSession $session): array
    {
        $totalPlayers = (int)(new Query())
            ->from('live_session_submission')
            ->where(['live_session_id' => $session->id])
            ->count('*');
        $firstCorrectBonuses = $this->getFirstCorrectBonuses((int)$session->id);
        $firstCorrectWinnersByQuestion = $firstCorrectBonuses['byQuestion'];

        $questionBonuses = [];
        if ($totalPlayers > 0) {
            $correctRows = (new Query())
                ->select([
                    'live_session_question_id',
                    'correct_count' => 'COUNT(*)',
                ])
                ->from('log')
                ->where([
                    'live_session_id' => $session->id,
                    'correct' => 1,
                ])
                ->groupBy(['live_session_question_id'])
                ->all();

            foreach ($correctRows as $row) {
                $questionBonuses[(int)$row['live_session_question_id']] = $this->calculateDifficultyBonus(
                    (int)$row['correct_count'],
                    $totalPlayers
                );
            }
        }

        $rows = (new Query())
            ->select([
                'submission_id' => 's.id',
                'first_name' => 's.first_name',
                'last_name' => 's.last_name',
                'class' => 's.class',
                'live_session_question_id' => 'l.live_session_question_id',
                'correct' => 'l.correct',
                'timestamp' => 'l.timestamp',
            ])
            ->from(['lss' => 'live_session_submission'])
            ->innerJoin(['s' => 'submission'], 's.id = lss.submission_id')
            ->leftJoin(['l' => 'log'], 'l.submission_id = s.id AND l.live_session_id = :sessionId')
            ->where(['lss.live_session_id' => $session->id])
            ->addParams([':sessionId' => $session->id])
            ->orderBy([
                's.id' => SORT_ASC,
                'l.timestamp' => SORT_ASC,
            ])
            ->all();

        $scores = [];
        foreach ($rows as $row) {
            $submissionId = (int)$row['submission_id'];
            if (!isset($scores[$submissionId])) {
                $scores[$submissionId] = [
                    'submission_id' => $submissionId,
                    'name' => trim((string)$row['first_name'] . ' ' . (string)$row['last_name']),
                    'class' => trim((string)$row['class']),
                    'score' => 0,
                    'correct_answers' => 0,
                    'latest_correct_at' => null,
                ];
            }

            if ((int)($row['correct'] ?? 0) !== 1) {
                continue;
            }

            $questionId = (int)($row['live_session_question_id'] ?? 0);
            $bonus = $questionBonuses[$questionId] ?? 0;
            $scores[$submissionId]['score'] += self::BASE_CORRECT_POINTS + $bonus;
            if (($firstCorrectWinnersByQuestion[$questionId] ?? null) === $submissionId) {
                $scores[$submissionId]['score'] += self::FIRST_CORRECT_BONUS_POINTS;
            }
            $scores[$submissionId]['correct_answers'] += 1;

            $timestamp = $row['timestamp'] ?? null;
            if ($timestamp !== null && ($scores[$submissionId]['latest_correct_at'] === null || $timestamp > $scores[$submissionId]['latest_correct_at'])) {
                $scores[$submissionId]['latest_correct_at'] = $timestamp;
            }
        }

        $leaderboard = array_values($scores);
        usort($leaderboard, static function (array $left, array $right): int {
            if ($left['score'] !== $right['score']) {
                return $right['score'] <=> $left['score'];
            }
            if ($left['correct_answers'] !== $right['correct_answers']) {
                return $right['correct_answers'] <=> $left['correct_answers'];
            }
            if ($left['latest_correct_at'] !== $right['latest_correct_at']) {
                return ($left['latest_correct_at'] ?? '9999-12-31 23:59:59') <=> ($right['latest_correct_at'] ?? '9999-12-31 23:59:59');
            }
            return $left['submission_id'] <=> $right['submission_id'];
        });

        foreach ($leaderboard as $index => &$entry) {
            $entry['rank'] = $index + 1;
        }
        unset($entry);

        return $leaderboard;
    }

    private function getFirstCorrectBonuses(int $sessionId): array
    {
        $rows = (new Query())
            ->select([
                'id',
                'submission_id',
                'live_session_question_id',
                'timestamp',
            ])
            ->from('log')
            ->where([
                'live_session_id' => $sessionId,
                'correct' => 1,
            ])
            ->andWhere(['not', ['live_session_question_id' => null]])
            ->orderBy([
                'live_session_question_id' => SORT_ASC,
                'timestamp' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->all();

        $counts = [];
        $winnersByQuestion = [];
        foreach ($rows as $row) {
            $questionId = (int)$row['live_session_question_id'];
            if (isset($winnersByQuestion[$questionId])) {
                continue;
            }

            $submissionId = (int)$row['submission_id'];
            $winnersByQuestion[$questionId] = $submissionId;
            $counts[$submissionId] = ($counts[$submissionId] ?? 0) + 1;
        }

        return [
            'counts' => $counts,
            'byQuestion' => $winnersByQuestion,
        ];
    }
}
