<?php

namespace tests\unit\live;

use app\live\models\LiveSession;
use app\live\models\LiveSessionQuestion;
use app\live\models\LiveSessionRankSnapshot;
use app\live\services\LiveLeaderboardService;
use Codeception\Test\Unit;

class LiveLeaderboardServiceTest extends Unit
{
    public function testBuildPresentationDataUsesSnapshotDeltas(): void
    {
        $session = new LiveSession([
            'id' => 7,
            'current_question_index' => 2,
        ]);
        $question = new LiveSessionQuestion([
            'id' => 9,
            'live_session_id' => 7,
            'question_order' => 2,
        ]);

        $service = $this->getMockBuilder(LiveLeaderboardService::class)
            ->onlyMethods(['buildLeaderboard'])
            ->getMock();

        $service->method('buildLeaderboard')->willReturn([
            ['rank' => 1, 'submission_id' => 11, 'name' => 'Ada Lovelace', 'class' => '5A', 'score' => 2, 'latest_correct_at' => '2026-05-27 10:00:00'],
            ['rank' => 2, 'submission_id' => 12, 'name' => 'Grace Hopper', 'class' => '5A', 'score' => 2, 'latest_correct_at' => '2026-05-27 10:00:01'],
            ['rank' => 3, 'submission_id' => 13, 'name' => 'Alan Turing', 'class' => '5B', 'score' => 1, 'latest_correct_at' => '2026-05-27 10:00:02'],
        ]);

        LiveSessionRankSnapshot::deleteAll(['live_session_id' => 7, 'question_order' => 2]);
        foreach ([
            ['submission_id' => 11, 'rank_position' => 1, 'previous_rank' => 2, 'rank_delta' => 1],
            ['submission_id' => 12, 'rank_position' => 2, 'previous_rank' => 1, 'rank_delta' => -1],
            ['submission_id' => 13, 'rank_position' => 3, 'previous_rank' => 5, 'rank_delta' => 2],
        ] as $row) {
            $snapshot = new LiveSessionRankSnapshot(array_merge($row, [
                'live_session_id' => 7,
                'live_session_question_id' => 9,
                'question_order' => 2,
                'score' => 2,
            ]));
            $snapshot->save(false);
        }

        $result = $service->buildPresentationData($session, $question);

        $this->assertCount(3, $result['top']);
        $this->assertSame('Alan Turing', $result['movers'][0]['name']);
        $this->assertSame(2, $result['movers'][0]['rank_delta']);
        $this->assertSame('Ada Lovelace', $result['top'][0]['name']);
        $this->assertSame(1, $result['top'][0]['rank_delta']);
    }
}
