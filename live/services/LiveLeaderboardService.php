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

    public function buildLeaderboard(LiveSession $session): array
    {
        $rows = (new Query())
            ->select([
                'submission_id' => 's.id',
                'first_name' => 's.first_name',
                'last_name' => 's.last_name',
                'class' => 's.class',
                'score' => 'COALESCE(SUM(CASE WHEN l.live_session_id = :sessionId THEN l.correct ELSE 0 END), 0)',
                'latest_correct_at' => 'MAX(CASE WHEN l.live_session_id = :sessionId AND l.correct = 1 THEN l.timestamp ELSE NULL END)',
            ])
            ->from(['lss' => 'live_session_submission'])
            ->innerJoin(['s' => 'submission'], 's.id = lss.submission_id')
            ->leftJoin(['l' => 'log'], 'l.submission_id = s.id AND l.live_session_id = :sessionId')
            ->where(['lss.live_session_id' => $session->id])
            ->groupBy(['s.id', 's.first_name', 's.last_name', 's.class'])
            ->orderBy([
                'score' => SORT_DESC,
                'latest_correct_at' => SORT_ASC,
                's.id' => SORT_ASC,
            ])
            ->addParams([':sessionId' => $session->id])
            ->all();

        $leaderboard = [];
        $rank = 0;
        foreach ($rows as $row) {
            $rank++;
            $leaderboard[] = [
                'rank' => $rank,
                'submission_id' => (int)$row['submission_id'],
                'name' => trim((string)$row['first_name'] . ' ' . (string)$row['last_name']),
                'class' => trim((string)$row['class']),
                'score' => (int)$row['score'],
                'latest_correct_at' => $row['latest_correct_at'],
            ];
        }

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
}
