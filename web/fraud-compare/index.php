<?php
declare(strict_types=1);

const SUSPICIOUS_SECONDS = 15;
const PERMUTATION_RUNS = 10000;

/**
 * @param mixed $value
 */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalizeInput(string $key): string
{
    $value = $_GET[$key] ?? '';
    return trim((string)$value);
}

function isDigits(string $value): bool
{
    return $value !== '' && ctype_digit($value);
}

function formatTimestamp(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '—';
    }

    return $value;
}

function formatBool(?int $value): string
{
    if ($value === null) {
        return '—';
    }

    return $value === 1 ? 'Yes' : 'No';
}

function formatDuration(?float $seconds): string
{
    if ($seconds === null) {
        return '—';
    }

    $totalSeconds = (int)round($seconds);
    $minutes = intdiv($totalSeconds, 60);
    $remainingSeconds = $totalSeconds % 60;

    return sprintf('%d:%02d', $minutes, $remainingSeconds);
}

function formatProbability(?float $value): string
{
    if ($value === null) {
        return '—';
    }

    if ($value <= 0.0) {
        return '0';
    }

    if ($value >= 0.001) {
        return number_format($value * 100, 4) . '%';
    }

    return sprintf('%.2e', $value);
}

function formatOneInOdds(?float $value): string
{
    if ($value === null) {
        return '—';
    }

    if ($value <= 0.0) {
        return '1:∞';
    }

    $inverse = 1 / $value;
    if ($inverse < 1000) {
        return '1:' . number_format(round($inverse), 0, '.', ',');
    }

    $scientific = sprintf('%.0E', $inverse);
    return '1:' . str_replace('E+', 'E+', strtoupper($scientific));
}

function formatPValue(?float $value): string
{
    if ($value === null) {
        return '—';
    }

    if ($value >= 0.001) {
        return number_format($value, 4);
    }

    return sprintf('%.2E', $value);
}

/**
 * @param array<int, ?float> $pValuesByIndex
 * @return array<int, ?float>
 */
function formatPercent(?float $value, int $decimals = 1): string
{
    if ($value === null) {
        return '—';
    }

    return number_format($value * 100, $decimals) . '%';
}

function formatSignedPercentPoints(?float $value, int $decimals = 1): string
{
    if ($value === null) {
        return '—';
    }

    $prefix = $value > 0 ? '+' : '';
    return $prefix . number_format($value * 100, $decimals) . '%';
}

function formatSignedSeconds(?int $seconds): string
{
    if ($seconds === null) {
        return '—';
    }

    if ($seconds > 0) {
        return '+' . $seconds . 's';
    }

    if ($seconds < 0) {
        return (string)$seconds . 's';
    }

    return '0s';
}

function formatCountAndPercent(?int $count, ?int $total): string
{
    if ($count === null || $total === null || $total <= 0) {
        return '—';
    }

    return $count . ' (' . number_format(($count / $total) * 100, 0) . '%)';
}

function formatInlineAnswerShare(?int $count, ?int $total): string
{
    if ($count === null || $total === null || $total <= 0) {
        return '';
    }

    return ' (' . number_format(($count / $total) * 100, 1) . '%)';
}

function binomialTailProbability(int $trials, int $successesOrMore, float $probability): ?float
{
    if ($trials < 0 || $successesOrMore < 0 || $successesOrMore > $trials) {
        return null;
    }

    if ($probability < 0.0 || $probability > 1.0) {
        return null;
    }

    if ($successesOrMore === 0) {
        return 1.0;
    }

    if ($probability === 0.0) {
        return 0.0;
    }

    if ($probability === 1.0) {
        return 1.0;
    }

    $tail = 0.0;
    for ($k = $successesOrMore; $k <= $trials; $k++) {
        $combination = 1.0;
        for ($i = 1; $i <= $k; $i++) {
            $combination *= ($trials - ($k - $i)) / $i;
        }

        $tail += $combination * ($probability ** $k) * ((1.0 - $probability) ** ($trials - $k));
    }

    return min(1.0, max(0.0, $tail));
}

function currentPageUrl(): string
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/fraud-compare/';
    return strtok($requestUri, '?') ?: '/fraud-compare/';
}

function parentUrl(): string
{
    $requestUri = rtrim($_SERVER['REQUEST_URI'] ?? '/fraud-compare/', '/');
    $parentPath = dirname($requestUri);
    $parentPath = str_replace('\\', '/', $parentPath);

    if ($parentPath === '.' || $parentPath === '/') {
        return '/';
    }

    return rtrim($parentPath, '/') . '/';
}

function buildCanonicalPairKey(string $leftStudentNr, string $rightStudentNr): string
{
    return strcmp($leftStudentNr, $rightStudentNr) <= 0
        ? $leftStudentNr . '|' . $rightStudentNr
        : $rightStudentNr . '|' . $leftStudentNr;
}

/**
 * @param array{student_1: array<string, mixed>, student_2: array<string, mixed>, summary: array<string, mixed>} $pair
 * @return array{student_1: array<string, mixed>, student_2: array<string, mixed>, summary: array<string, mixed>}
 */
function placeDominantLeaderFirst(array $pair): array
{
    if (($pair['summary']['dominant_close_leader'] ?? null) !== 'student_2') {
        return $pair;
    }

    return [
        'student_1' => $pair['student_2'],
        'student_2' => $pair['student_1'],
        'summary' => $pair['summary'],
    ];
}

function comparePairExtremeness(array $leftSummary, array $rightSummary): int
{
    $comparisons = [
        (($leftSummary['close_ratio'] ?? 0.0) <=> ($rightSummary['close_ratio'] ?? 0.0)),
        (($leftSummary['dominant_leader_ratio'] ?? -1.0) <=> ($rightSummary['dominant_leader_ratio'] ?? -1.0)),
        (($leftSummary['longest_same_leader_close_run'] ?? 0) <=> ($rightSummary['longest_same_leader_close_run'] ?? 0)),
        (($leftSummary['longest_close_streak'] ?? 0) <=> ($rightSummary['longest_close_streak'] ?? 0)),
        (($leftSummary['close_rows'] ?? 0) <=> ($rightSummary['close_rows'] ?? 0)),
    ];

    foreach ($comparisons as $comparison) {
        if ($comparison !== 0) {
            return $comparison;
        }
    }

    return 0;
}

/**
 * @param array<int, string> $studentIds
 * @return array<int, string>
 */
function buildDerangement(array $studentIds): array
{
    $count = count($studentIds);
    if ($count <= 1) {
        return $studentIds;
    }

    if ($count === 2) {
        return [$studentIds[1], $studentIds[0]];
    }

    $candidate = $studentIds;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $candidate = $studentIds;
        shuffle($candidate);
        $isDerangement = true;
        foreach ($studentIds as $index => $studentId) {
            if ($candidate[$index] === $studentId) {
                $isDerangement = false;
                break;
            }
        }

        if ($isDerangement) {
            return $candidate;
        }
    }

    $candidate = $studentIds;
    $first = array_shift($candidate);
    if ($first !== null) {
        $candidate[] = $first;
    }

    return $candidate;
}

/**
 * @param array<int, array{label: string, question: string}> $questionMeta
 * @param array<int, array{question_id:int, question_no:?int, answer_no:?int, correct:?int, timestamp:?string}> $leftLogs
 * @param array<int, array{question_id:int, question_no:?int, answer_no:?int, correct:?int, timestamp:?string}> $rightLogs
 * @return array<int, array<string, mixed>>
 */
function buildComparisonRows(array $questionMeta, array $leftLogs, array $rightLogs): array
{
    $comparisonRows = [];
    $questionIds = array_unique(array_merge(
        array_map('intval', array_keys($leftLogs)),
        array_map('intval', array_keys($rightLogs))
    ));

    foreach ($questionIds as $questionId) {
        $left = $leftLogs[$questionId] ?? null;
        $right = $rightLogs[$questionId] ?? null;
        $diffSeconds = null;

        if ($left !== null && $right !== null && $left['timestamp'] !== null && $right['timestamp'] !== null) {
            $signedDiffSeconds = strtotime($left['timestamp']) - strtotime($right['timestamp']);
            $diffSeconds = abs($signedDiffSeconds);
        } else {
            $signedDiffSeconds = null;
        }

        $closeLeader = null;
        if ($diffSeconds !== null && $diffSeconds <= SUSPICIOUS_SECONDS && $signedDiffSeconds !== null) {
            if ($signedDiffSeconds < 0) {
                $closeLeader = 'student_1';
            } elseif ($signedDiffSeconds > 0) {
                $closeLeader = 'student_2';
            } else {
                $closeLeader = 'tie';
            }
        }

        $comparisonRows[] = [
            'question_id' => $questionId,
            'label' => $questionMeta[$questionId]['label'] ?? '',
            'question' => $questionMeta[$questionId]['question'] ?? '',
            'student_1' => $left,
            'student_2' => $right,
            'diff_seconds' => $diffSeconds,
            'signed_diff_seconds' => $signedDiffSeconds,
            'is_close' => $diffSeconds !== null && $diffSeconds <= SUSPICIOUS_SECONDS,
            'close_leader' => $closeLeader,
        ];
    }

    usort($comparisonRows, static function (array $left, array $right): int {
        $leftOrder = min(
            $left['student_1']['question_no'] ?? PHP_INT_MAX,
            $left['student_2']['question_no'] ?? PHP_INT_MAX
        );
        $rightOrder = min(
            $right['student_1']['question_no'] ?? PHP_INT_MAX,
            $right['student_2']['question_no'] ?? PHP_INT_MAX
        );

        if ($leftOrder === $rightOrder) {
            return $left['question_id'] <=> $right['question_id'];
        }

        return $leftOrder <=> $rightOrder;
    });

    return $comparisonRows;
}

/**
 * @param array<int, array<string, mixed>> $comparisonRows
 * @return array{matched_questions:int, close_rows:int, smallest_diff:?int, average_diff:?float, close_ratio:float, longest_close_streak:int, close_rows_with_leader:int, close_rows_with_wrong_answer:int, dominant_close_leader:?string, dominant_close_lead_count:int, dominant_leader_ratio:?float, longest_same_leader_close_run:int, dominant_leader_same_answer_count:int, dominant_leader_same_wrong_answer_count:int}
 */
function buildSummary(array $comparisonRows): array
{
    $matchedRows = array_values(array_filter(
        $comparisonRows,
        static fn(array $row): bool => $row['diff_seconds'] !== null
    ));

    $closeRows = array_values(array_filter(
        $matchedRows,
        static fn(array $row): bool => $row['is_close'] === true
    ));

    $diffValues = array_map(
        static fn(array $row): int => (int)$row['diff_seconds'],
        $matchedRows
    );

    $matchedCount = count($matchedRows);
    $closeCount = count($closeRows);
    $currentCloseStreak = 0;
    $longestCloseStreak = 0;
    $closeLeaderCounts = [
        'student_1' => 0,
        'student_2' => 0,
    ];
    $closeRowsWithLeader = 0;
    $closeRowsWithWrongAnswer = 0;
    $currentSameLeaderCloseRun = 0;
    $longestSameLeaderCloseRun = 0;
    $previousCloseLeader = null;

    foreach ($matchedRows as $row) {
        if ($row['is_close'] === true) {
            $currentCloseStreak++;
            if ($currentCloseStreak > $longestCloseStreak) {
                $longestCloseStreak = $currentCloseStreak;
            }

            $leftCorrect = $row['student_1']['correct'] ?? null;
            $rightCorrect = $row['student_2']['correct'] ?? null;
            if ($leftCorrect === 0 || $rightCorrect === 0) {
                $closeRowsWithWrongAnswer++;
            }

            $closeLeader = $row['close_leader'] ?? null;
            if ($closeLeader === 'student_1' || $closeLeader === 'student_2') {
                $closeLeaderCounts[$closeLeader]++;
                $closeRowsWithLeader++;

                if ($previousCloseLeader === $closeLeader) {
                    $currentSameLeaderCloseRun++;
                } else {
                    $currentSameLeaderCloseRun = 1;
                }

                if ($currentSameLeaderCloseRun > $longestSameLeaderCloseRun) {
                    $longestSameLeaderCloseRun = $currentSameLeaderCloseRun;
                }

                $previousCloseLeader = $closeLeader;
            } else {
                $currentSameLeaderCloseRun = 0;
                $previousCloseLeader = null;
            }
        } else {
            $currentCloseStreak = 0;
            $currentSameLeaderCloseRun = 0;
            $previousCloseLeader = null;
        }
    }

    $dominantCloseLeader = null;
    $dominantCloseLeadCount = 0;
    if ($closeLeaderCounts['student_1'] > $closeLeaderCounts['student_2']) {
        $dominantCloseLeader = 'student_1';
        $dominantCloseLeadCount = $closeLeaderCounts['student_1'];
    } elseif ($closeLeaderCounts['student_2'] > $closeLeaderCounts['student_1']) {
        $dominantCloseLeader = 'student_2';
        $dominantCloseLeadCount = $closeLeaderCounts['student_2'];
    } elseif ($closeLeaderCounts['student_1'] > 0) {
        $dominantCloseLeader = 'tie';
        $dominantCloseLeadCount = $closeLeaderCounts['student_1'];
    }

    $dominantLeaderSameAnswerCount = 0;
    $dominantLeaderSameWrongAnswerCount = 0;

    if ($dominantCloseLeader === 'student_1' || $dominantCloseLeader === 'student_2') {
        foreach ($closeRows as $row) {
            if (($row['close_leader'] ?? null) !== $dominantCloseLeader) {
                continue;
            }

            $leftAnswer = $row['student_1']['answer_no'] ?? null;
            $rightAnswer = $row['student_2']['answer_no'] ?? null;
            if ($leftAnswer === null || $rightAnswer === null || $leftAnswer !== $rightAnswer) {
                continue;
            }

            $dominantLeaderSameAnswerCount++;

            $leftCorrect = $row['student_1']['correct'] ?? null;
            $rightCorrect = $row['student_2']['correct'] ?? null;
            if ($leftCorrect === 0 && $rightCorrect === 0) {
                $dominantLeaderSameWrongAnswerCount++;
            }
        }
    }

    return [
        'matched_questions' => $matchedCount,
        'close_rows' => $closeCount,
        'smallest_diff' => $diffValues !== [] ? min($diffValues) : null,
        'average_diff' => $diffValues !== [] ? (array_sum($diffValues) / count($diffValues)) : null,
        'close_ratio' => $matchedCount > 0 ? $closeCount / $matchedCount : 0.0,
        'longest_close_streak' => $longestCloseStreak,
        'close_rows_with_leader' => $closeRowsWithLeader,
        'close_rows_with_wrong_answer' => $closeRowsWithWrongAnswer,
        'dominant_close_leader' => $dominantCloseLeader,
        'dominant_close_lead_count' => $dominantCloseLeadCount,
        'dominant_leader_ratio' => $closeRowsWithLeader > 0 ? $dominantCloseLeadCount / $closeRowsWithLeader : null,
        'longest_same_leader_close_run' => $longestSameLeaderCloseRun,
        'dominant_leader_same_answer_count' => $dominantLeaderSameAnswerCount,
        'dominant_leader_same_wrong_answer_count' => $dominantLeaderSameWrongAnswerCount,
    ];
}

/**
 * @param array<string, mixed> $leftSubmission
 * @param array<string, mixed> $rightSubmission
 * @param array<int, array<int, array{question_id:int, question_no:?int, answer_no:?int, correct:?int, timestamp:?string}>> $logsBySubmissionId
 * @param array<int, array{label: string, question: string}> $questionMeta
 * @return array{rows: array<int, array<string, mixed>>, summary: array{matched_questions:int, close_rows:int, smallest_diff:?int, average_diff:?float, close_ratio:float, longest_close_streak:int, close_rows_with_leader:int, close_rows_with_wrong_answer:int, dominant_close_leader:?string, dominant_close_lead_count:int, dominant_leader_ratio:?float, longest_same_leader_close_run:int, dominant_leader_same_answer_count:int, dominant_leader_same_wrong_answer_count:int}}
 */
function analyzeSubmissionPair(array $leftSubmission, array $rightSubmission, array $logsBySubmissionId, array $questionMeta): array
{
    $comparisonRows = buildComparisonRows(
        $questionMeta,
        $logsBySubmissionId[(int)$leftSubmission['id']] ?? [],
        $logsBySubmissionId[(int)$rightSubmission['id']] ?? []
    );

    return [
        'rows' => $comparisonRows,
        'summary' => buildSummary($comparisonRows),
    ];
}

/**
 * @param array<int, array<string, mixed>> $submissions
 * @param array<int, array<int, array{question_id:int, question_no:?int, answer_no:?int, correct:?int, timestamp:?string}>> $logsBySubmissionId
 * @return array{count:int, average_seconds:?float, stdev_seconds:?float}
 */
function buildQuestionTimingStats(array $submissions, array $logsBySubmissionId): array
{
    $durations = [];

    foreach ($submissions as $submission) {
        $startTime = $submission['start_time'] ?? null;
        if (!is_string($startTime) || trim($startTime) === '') {
            continue;
        }

        $startTimestamp = strtotime($startTime);
        $logs = $logsBySubmissionId[(int)$submission['id']] ?? [];

        $previousTimestamp = $startTimestamp;
        foreach ($logs as $entry) {
            $answerTimestamp = $entry['timestamp'] ?? null;
            if (!is_string($answerTimestamp) || trim($answerTimestamp) === '') {
                continue;
            }

            $currentTimestamp = strtotime($answerTimestamp);
            $duration = $currentTimestamp - $previousTimestamp;
            if ($duration >= 0) {
                $durations[] = (float)$duration;
            }

            $previousTimestamp = $currentTimestamp;
        }
    }

    $count = count($durations);
    if ($count === 0) {
        return [
            'count' => 0,
            'average_seconds' => null,
            'stdev_seconds' => null,
        ];
    }

    $average = array_sum($durations) / $count;
    if ($count === 1) {
        $stdev = 0.0;
    } else {
        $varianceSum = 0.0;
        foreach ($durations as $duration) {
            $varianceSum += ($duration - $average) ** 2;
        }
        $stdev = sqrt($varianceSum / ($count - 1));
    }

    return [
        'count' => $count,
        'average_seconds' => $average,
        'stdev_seconds' => $stdev,
    ];
}

$quizIdInput = normalizeInput('quiz_id');
$student1Input = normalizeInput('student_1');
$student2Input = normalizeInput('student_2');
$actionInput = normalizeInput('action');
$thresholdOptions = [10, 25, 50, 75, 90];
$thresholdInput = normalizeInput('close_threshold');
$selectedThresholdPercent = in_array((int)$thresholdInput, $thresholdOptions, true) ? (int)$thresholdInput : 50;
$selectedThresholdRatio = $selectedThresholdPercent / 100;
$activeAction = $actionInput === 'all_pairs' ? 'all_pairs' : 'compare';
$hasQuizLookup = $quizIdInput !== '';
$submitted = $quizIdInput !== '' || $student1Input !== '' || $student2Input !== '';

$errors = [];
$connectionError = null;
$queryError = null;
$quiz = null;
$studentOptions = [];
$selectedSubmissions = [];
$latestSubmissions = [];
$logsBySubmissionId = [];
$questionMeta = [];
$answerStatsByQuestion = [];
$comparisonRows = [];
$summary = null;
$potentialFraudPairs = [];
$durationStats = null;
$baselineStats = null;
$permutationNullScores = [];
$comparePairSignals = null;

if ($submitted) {
    if (!isDigits($quizIdInput)) {
        $errors[] = 'Quiz ID must be a number.';
    }
    if ($activeAction === 'compare' && $student1Input !== '' && !isDigits($student1Input)) {
        $errors[] = 'Student 1 must be a number.';
    }
    if ($activeAction === 'compare' && $student2Input !== '' && !isDigits($student2Input)) {
        $errors[] = 'Student 2 must be a number.';
    }
    if ($activeAction === 'compare' && $student1Input !== '' && $student2Input !== '' && $student1Input === $student2Input) {
        $errors[] = 'Student 1 and Student 2 must be different.';
    }
    if ($activeAction === 'compare' && ($student1Input !== '' || $student2Input !== '') && ($student1Input === '' || $student2Input === '')) {
        $errors[] = 'Select both students to run a direct comparison.';
    }
}

if ($submitted && $errors === []) {
    $dbConfigPath = dirname(__DIR__, 2) . '/config/db.php';

    try {
        /** @var array{dsn?: string, username?: string, password?: string} $dbConfig */
        $dbConfig = require $dbConfigPath;
        $pdo = new PDO(
            (string)($dbConfig['dsn'] ?? ''),
            (string)($dbConfig['username'] ?? ''),
            (string)($dbConfig['password'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $exception) {
        $connectionError = $exception->getMessage();
    }

    if ($connectionError === null) {
        try {
            $quizStmt = $pdo->prepare('
                SELECT id, name, quiz_group, language, no_questions
                FROM quiz
                WHERE id = :quiz_id
                LIMIT 1
            ');
            $quizStmt->execute(['quiz_id' => (int)$quizIdInput]);
            $quiz = $quizStmt->fetch() ?: null;

            $studentOptionStmt = $pdo->prepare('
                SELECT
                    s.id,
                    s.student_nr,
                    s.first_name,
                    s.last_name,
                    s.class,
                    s.start_time,
                    s.end_time,
                    s.last_updated,
                    s.no_answered,
                    s.no_correct,
                    s.no_questions,
                    s.quiz_id,
                    COALESCE(s.end_time, s.last_updated, s.start_time) AS sort_time
                FROM submission s
                WHERE s.quiz_id = :quiz_id
                  AND s.student_nr IS NOT NULL
                ORDER BY s.student_nr ASC, sort_time DESC, s.id DESC
            ');
            $studentOptionStmt->execute(['quiz_id' => (int)$quizIdInput]);

            foreach ($studentOptionStmt->fetchAll() as $studentRow) {
                $studentNr = trim((string)$studentRow['student_nr']);
                if ($studentNr === '' || isset($studentOptions[$studentNr])) {
                    continue;
                }

                $latestSubmissions[$studentNr] = $studentRow;
                $studentOptions[$studentNr] = [
                    'student_nr' => $studentNr,
                    'full_name' => trim((string)$studentRow['first_name'] . ' ' . (string)$studentRow['last_name']),
                    'class' => trim((string)$studentRow['class']),
                ];
            }

            uasort($studentOptions, static function (array $left, array $right): int {
                $leftName = mb_strtolower(trim($left['full_name']));
                $rightName = mb_strtolower(trim($right['full_name']));

                if ($leftName === $rightName) {
                    return strcmp($left['student_nr'], $right['student_nr']);
                }

                return $leftName <=> $rightName;
            });

            if ($latestSubmissions !== []) {
                $submissionIds = array_map(
                    static fn(array $submission): int => (int)$submission['id'],
                    array_values($latestSubmissions)
                );
                $logPlaceholders = implode(',', array_fill(0, count($submissionIds), '?'));
                $logStmt = $pdo->prepare("
                    SELECT
                        l.id,
                        l.submission_id,
                        l.question_id,
                        l.no_answered,
                        l.answer_no,
                        l.correct,
                        l.timestamp,
                        q.label,
                        q.question
                    FROM log l
                    LEFT JOIN question q
                      ON q.id = l.question_id
                    WHERE l.submission_id IN ($logPlaceholders)
                    ORDER BY l.submission_id ASC, l.no_answered ASC, l.id ASC
                ");
                $logStmt->execute($submissionIds);

                foreach ($logStmt->fetchAll() as $logRow) {
                    $submissionId = (int)$logRow['submission_id'];
                    $questionId = (int)$logRow['question_id'];
                    $answerNo = isset($logRow['answer_no']) ? (int)$logRow['answer_no'] : null;
                    $logsBySubmissionId[$submissionId][$questionId] = [
                        'question_id' => $questionId,
                        'question_no' => isset($logRow['no_answered']) ? ((int)$logRow['no_answered'] + 1) : null,
                        'answer_no' => $answerNo,
                        'correct' => isset($logRow['correct']) ? (int)$logRow['correct'] : null,
                        'timestamp' => $logRow['timestamp'] ?? null,
                    ];

                    if (!isset($answerStatsByQuestion[$questionId])) {
                        $answerStatsByQuestion[$questionId] = [
                            'total' => 0,
                            'wrong_total' => 0,
                            'answers' => [],
                        ];
                    }
                    $answerStatsByQuestion[$questionId]['total']++;
                    if (($logRow['correct'] ?? null) !== null && (int)$logRow['correct'] === 0) {
                        $answerStatsByQuestion[$questionId]['wrong_total']++;
                    }
                    if ($answerNo !== null) {
                        if (!isset($answerStatsByQuestion[$questionId]['answers'][$answerNo])) {
                            $answerStatsByQuestion[$questionId]['answers'][$answerNo] = 0;
                        }
                        $answerStatsByQuestion[$questionId]['answers'][$answerNo]++;
                    }

                    if (!isset($questionMeta[$questionId])) {
                        $questionMeta[$questionId] = [
                            'label' => trim((string)($logRow['label'] ?? '')),
                            'question' => trim((string)($logRow['question'] ?? '')),
                        ];
                    }
                }
            }

            if ($activeAction === 'compare' && $student1Input !== '' && $student2Input !== '') {
                $student1Submission = $latestSubmissions[$student1Input] ?? null;
                $student2Submission = $latestSubmissions[$student2Input] ?? null;
                $selectedSubmissions = array_filter([
                    $student1Input => $student1Submission,
                    $student2Input => $student2Submission,
                ]);

                if ($student1Submission !== null && $student2Submission !== null) {
                    $pairAnalysis = analyzeSubmissionPair($student1Submission, $student2Submission, $logsBySubmissionId, $questionMeta);
                    $comparisonRows = $pairAnalysis['rows'];
                    $summary = $pairAnalysis['summary'];

                    $students = array_values($latestSubmissions);
                    $studentCount = count($students);
                    if ($studentCount >= 2) {
                        $baselineMatchedQuestions = 0;
                        $baselineCloseRows = 0;
                        $baselineProbability = null;
                        $pairSummaryByKey = [];
                        $studentIds = [];

                        foreach ($students as $student) {
                            $studentIds[] = (string)$student['student_nr'];
                        }

                        for ($leftIndex = 0; $leftIndex < $studentCount - 1; $leftIndex++) {
                            for ($rightIndex = $leftIndex + 1; $rightIndex < $studentCount; $rightIndex++) {
                                $leftSubmission = $students[$leftIndex];
                                $rightSubmission = $students[$rightIndex];
                                $allPairAnalysis = analyzeSubmissionPair($leftSubmission, $rightSubmission, $logsBySubmissionId, $questionMeta);
                                $allPairSummary = $allPairAnalysis['summary'];
                                $pairKey = buildCanonicalPairKey((string)$leftSubmission['student_nr'], (string)$rightSubmission['student_nr']);
                                $pairSummaryByKey[$pairKey] = $allPairSummary;

                                if ($allPairSummary['matched_questions'] > 0) {
                                    $baselineMatchedQuestions += $allPairSummary['matched_questions'];
                                    $baselineCloseRows += $allPairSummary['close_rows'];
                                }
                            }
                        }

                        if ($baselineMatchedQuestions > 0) {
                            $baselineProbability = $baselineCloseRows / $baselineMatchedQuestions;
                        }

                        $comparePermutationNullScores = [];
                        if ($pairSummaryByKey !== []) {
                            for ($run = 0; $run < PERMUTATION_RUNS; $run++) {
                                $permutedStudentIds = buildDerangement($studentIds);
                                $seenPairKeys = [];

                                foreach ($studentIds as $index => $leftStudentId) {
                                    $rightStudentId = $permutedStudentIds[$index] ?? null;
                                    if ($rightStudentId === null) {
                                        continue;
                                    }

                                    $pairKey = buildCanonicalPairKey($leftStudentId, $rightStudentId);
                                    if (isset($seenPairKeys[$pairKey]) || !isset($pairSummaryByKey[$pairKey])) {
                                        continue;
                                    }

                                    $seenPairKeys[$pairKey] = true;
                                    $comparePermutationNullScores[] = $pairSummaryByKey[$pairKey];
                                }
                            }
                        }

                        $selectedPairKey = buildCanonicalPairKey((string)$student1Submission['student_nr'], (string)$student2Submission['student_nr']);
                        $selectedPairPermutationSummary = $pairSummaryByKey[$selectedPairKey] ?? $summary;
                        $selectedPairPValue = null;
                        if ($comparePermutationNullScores !== []) {
                            $permutationExtremeCount = 0;
                            foreach ($comparePermutationNullScores as $nullSummary) {
                                if (comparePairExtremeness($nullSummary, $selectedPairPermutationSummary) >= 0) {
                                    $permutationExtremeCount++;
                                }
                            }
                            $selectedPairPValue = (1 + $permutationExtremeCount) / (1 + count($comparePermutationNullScores));
                        }

                        $comparePairSignals = [
                            'matched_questions' => $summary['matched_questions'],
                            'close_rows' => $summary['close_rows'],
                            'close_ratio' => $summary['close_ratio'],
                            'longest_close_streak' => $summary['longest_close_streak'],
                            'close_rows_with_leader' => $summary['close_rows_with_leader'],
                            'close_rows_with_wrong_answer' => $summary['close_rows_with_wrong_answer'],
                            'dominant_close_leader' => $summary['dominant_close_leader'],
                            'dominant_close_lead_count' => $summary['dominant_close_lead_count'],
                            'dominant_leader_ratio' => $summary['dominant_leader_ratio'],
                            'longest_same_leader_close_run' => $summary['longest_same_leader_close_run'],
                            'dominant_leader_same_answer_count' => $summary['dominant_leader_same_answer_count'],
                            'dominant_leader_same_wrong_answer_count' => $summary['dominant_leader_same_wrong_answer_count'],
                            'smallest_diff' => $summary['smallest_diff'],
                            'average_diff' => $summary['average_diff'],
                            'expected_close_ratio' => $baselineProbability,
                            'permutation_p_value' => $selectedPairPValue,
                            'rarity_one_in' => $baselineProbability !== null
                                ? binomialTailProbability(
                                    (int)$summary['matched_questions'],
                                    (int)$summary['close_rows'],
                                    (float)$baselineProbability
                                )
                                : null,
                        ];
                    }
                }
            }

            if ($activeAction === 'all_pairs') {
                $students = array_values($latestSubmissions);
                $studentCount = count($students);
                $durationStats = buildQuestionTimingStats($students, $logsBySubmissionId);
                $baselineMatchedQuestions = 0;
                $baselineCloseRows = 0;
                $baselinePairsCompared = 0;

                if ($studentCount < 2) {
                    $errors[] = 'At least two students with submissions are required for all-student comparison.';
                } else {
                    $baselineProbability = null;
                    $pairSummaryByKey = [];
                    $studentIds = [];
                    foreach ($students as $student) {
                        $studentIds[] = (string)$student['student_nr'];
                    }

                    for ($leftIndex = 0; $leftIndex < $studentCount - 1; $leftIndex++) {
                        for ($rightIndex = $leftIndex + 1; $rightIndex < $studentCount; $rightIndex++) {
                            $leftSubmission = $students[$leftIndex];
                            $rightSubmission = $students[$rightIndex];
                            $pairAnalysis = analyzeSubmissionPair($leftSubmission, $rightSubmission, $logsBySubmissionId, $questionMeta);
                            $pairSummary = $pairAnalysis['summary'];
                            $pairKey = buildCanonicalPairKey((string)$leftSubmission['student_nr'], (string)$rightSubmission['student_nr']);
                            $pairSummaryByKey[$pairKey] = $pairSummary;
                            if ($pairSummary['matched_questions'] > 0) {
                                $baselinePairsCompared++;
                                $baselineMatchedQuestions += $pairSummary['matched_questions'];
                                $baselineCloseRows += $pairSummary['close_rows'];
                            }

                            if ($pairSummary['matched_questions'] > 0 && $pairSummary['close_ratio'] > $selectedThresholdRatio) {
                                $potentialFraudPairs[] = placeDominantLeaderFirst([
                                    'student_1' => $leftSubmission,
                                    'student_2' => $rightSubmission,
                                    'summary' => $pairSummary,
                                ]);
                            }
                        }
                    }

                    if ($baselineMatchedQuestions > 0) {
                        $baselineProbability = $baselineCloseRows / $baselineMatchedQuestions;
                    }

                    if ($studentCount >= 2 && $pairSummaryByKey !== []) {
                        for ($run = 0; $run < PERMUTATION_RUNS; $run++) {
                            $permutedStudentIds = buildDerangement($studentIds);
                            $seenPairKeys = [];

                            foreach ($studentIds as $index => $leftStudentId) {
                                $rightStudentId = $permutedStudentIds[$index] ?? null;
                                if ($rightStudentId === null) {
                                    continue;
                                }

                                $pairKey = buildCanonicalPairKey($leftStudentId, $rightStudentId);
                                if (isset($seenPairKeys[$pairKey]) || !isset($pairSummaryByKey[$pairKey])) {
                                    continue;
                                }

                                $seenPairKeys[$pairKey] = true;
                                $permutationNullScores[] = $pairSummaryByKey[$pairKey];
                            }
                        }
                    }

                    foreach ($potentialFraudPairs as &$pair) {
                        $pairSummary = $pair['summary'];
                        $pair['same_window_chance'] = $baselineProbability !== null
                            ? binomialTailProbability(
                                (int)$pairSummary['matched_questions'],
                                (int)$pairSummary['close_rows'],
                                (float)$baselineProbability
                            )
                            : null;
                        $permutationExtremeCount = 0;
                        foreach ($permutationNullScores as $nullSummary) {
                            if (comparePairExtremeness($nullSummary, $pairSummary) >= 0) {
                                $permutationExtremeCount++;
                            }
                        }
                        $pair['permutation_p_value'] = $permutationNullScores !== []
                            ? (1 + $permutationExtremeCount) / (1 + count($permutationNullScores))
                            : null;
                    }
                    unset($pair);

                    usort($potentialFraudPairs, static function (array $left, array $right): int {
                        $ratioCompare = $right['summary']['close_ratio'] <=> $left['summary']['close_ratio'];
                        if ($ratioCompare !== 0) {
                            return $ratioCompare;
                        }

                        $streakCompare = $right['summary']['longest_close_streak'] <=> $left['summary']['longest_close_streak'];
                        if ($streakCompare !== 0) {
                            return $streakCompare;
                        }

                        $closeCompare = $right['summary']['close_rows'] <=> $left['summary']['close_rows'];
                        if ($closeCompare !== 0) {
                            return $closeCompare;
                        }

                        return strcmp(
                            trim((string)$left['student_1']['first_name'] . ' ' . (string)$left['student_1']['last_name']),
                            trim((string)$right['student_1']['first_name'] . ' ' . (string)$right['student_1']['last_name'])
                        );
                    });

                    $baselineStats = [
                        'pairs_compared' => $baselinePairsCompared,
                        'matched_questions' => $baselineMatchedQuestions,
                        'close_rows' => $baselineCloseRows,
                        'close_probability' => $baselineMatchedQuestions > 0 ? $baselineCloseRows / $baselineMatchedQuestions : null,
                    ];
                }
            }
        } catch (PDOException $exception) {
            $queryError = $exception->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Fraud Compare</title>
    <style>
        :root {
            --bg: #f3efe6;
            --panel: #fffdf8;
            --panel-strong: #f6f1e7;
            --text: #1e1b18;
            --muted: #6f655d;
            --line: #d8ccbc;
            --accent: #165d56;
            --accent-soft: #dff1ee;
            --warn: #9c4f19;
            --warn-soft: #fde9d8;
            --danger: #8b2e2e;
            --danger-soft: #f9e1df;
            --shadow: 0 20px 60px rgba(52, 39, 20, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(22, 93, 86, 0.16), transparent 30%),
                radial-gradient(circle at top right, rgba(156, 79, 25, 0.12), transparent 28%),
                linear-gradient(180deg, #f7f4ee 0%, var(--bg) 100%);
            color: var(--text);
        }

        .page {
            width: min(1200px, calc(100% - 32px));
            margin: 32px auto 56px;
        }

        .shell {
            background: rgba(255, 253, 248, 0.85);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(216, 204, 188, 0.8);
            border-radius: 28px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .hero {
            padding: 28px 28px 24px;
            background: linear-gradient(135deg, rgba(22, 93, 86, 0.1), rgba(255, 255, 255, 0));
            border-bottom: 1px solid var(--line);
        }

        .hero-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        h1 {
            margin: 18px 0 6px;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1;
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
            max-width: 760px;
            line-height: 1.5;
        }

        .threshold {
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--panel);
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .content {
            padding: 28px;
            display: grid;
            gap: 24px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 22px;
        }

        .card h2 {
            margin: 0 0 16px;
            font-size: 1.2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        label {
            display: block;
            font-size: 0.92rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--muted);
        }

        input,
        select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 1rem;
            background: #fff;
            color: var(--text);
        }

        input:focus,
        select:focus {
            outline: 2px solid rgba(22, 93, 86, 0.22);
            border-color: var(--accent);
        }

        .actions {
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .button {
            appearance: none;
            border: 0;
            border-radius: 14px;
            padding: 14px 18px;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .button.secondary {
            text-decoration: none;
            background: var(--panel-strong);
            color: var(--text);
            border: 1px solid var(--line);
        }

        .messages {
            display: grid;
            gap: 12px;
        }

        .message {
            border-radius: 18px;
            padding: 14px 16px;
            border: 1px solid;
        }

        .message.error {
            background: var(--danger-soft);
            border-color: rgba(139, 46, 46, 0.22);
            color: var(--danger);
        }

        .message.warn {
            background: var(--warn-soft);
            border-color: rgba(156, 79, 25, 0.22);
            color: var(--warn);
        }

        .meta-grid,
        .summary-grid {
            display: grid;
            gap: 16px;
        }

        .meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .pair-table {
            min-width: 880px;
        }

        .pair-link-row {
            color: inherit;
            text-decoration: none;
            display: contents;
        }

        .metric,
        .submission-card {
            border-radius: 18px;
            background: var(--panel-strong);
            border: 1px solid var(--line);
            padding: 16px;
        }

        .eyebrow {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .metric strong,
        .submission-card strong {
            font-size: 1.1rem;
        }

        .metric small {
            display: block;
            margin-top: 6px;
        }

        .submission-card dl {
            margin: 12px 0 0;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 6px 12px;
        }

        .submission-card dt {
            color: var(--muted);
        }

        .submission-card dd {
            margin: 0;
            font-weight: 600;
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 20px;
            border: 1px solid var(--line);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1020px;
            background: #fff;
        }

        thead th {
            text-align: left;
            font-size: 0.85rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
            background: #f8f3ea;
        }

        th,
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #eadfce;
            vertical-align: top;
        }

        tbody tr.close-row {
            background: #fff4ec;
        }

        tbody tr.missing-row {
            background: #faf8f3;
        }

        tbody tr.clickable-row {
            cursor: pointer;
            transition: background-color 0.16s ease;
        }

        tbody tr.clickable-row:hover,
        tbody tr.clickable-row:focus-within {
            background: #ffe9da;
        }

        .question-label {
            display: inline-block;
            margin-bottom: 6px;
            padding: 4px 8px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .question-preview {
            max-width: 320px;
            color: var(--muted);
            line-height: 1.4;
        }

        .cell-stack {
            display: grid;
            gap: 6px;
        }

        .earliest-timestamp {
            color: #2f8f46;
            font-weight: 700;
        }

        .cell-stack small {
            color: var(--muted);
        }

        .correct-status {
            font-weight: 700;
        }

        .correct-status.yes {
            color: #2f8f46;
        }

        .correct-status.no {
            color: #b43a2f;
        }

        .flag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .flag.close {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .flag.close-same {
            background: #ffe7c7;
            color: #9c5a10;
        }

        .flag.close-same-wrong {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .flag.missing {
            background: #ebe6dd;
            color: var(--muted);
        }

        .muted {
            color: var(--muted);
        }

        .help-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            position: relative;
            cursor: help;
        }

        .help-label::after {
            content: attr(data-help);
            position: absolute;
            left: 50%;
            top: calc(100% + 10px);
            transform: translateX(-50%);
            width: min(300px, 70vw);
            padding: 10px 12px;
            border-radius: 12px;
            background: #1f1b17;
            color: #fff;
            font-size: 0.78rem;
            line-height: 1.4;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
            white-space: normal;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease;
            z-index: 20;
        }

        .help-label:hover::after,
        .help-label:focus-visible::after {
            opacity: 1;
        }

        @media (max-width: 900px) {
            .form-grid,
            .meta-grid,
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .hero-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .content,
            .hero {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="shell">
            <header class="hero">
                <div class="hero-top">
                    <a class="back-link" href="<?= h(parentUrl()) ?>">&larr; Back</a>
                    <div class="threshold">Suspicious if answers are within <?= h((string)SUSPICIOUS_SECONDS) ?> seconds</div>
                </div>
                <h1>Quiz Fraud Compare</h1>
                <p class="subtitle">
                    Compare the latest classic self-paced quiz submission for two students in one quiz.
                    Each answer is matched by question, then the submit-time gap is calculated to highlight suspiciously close timing.
                </p>
            </header>

            <div class="content">
                <section class="card">
                    <h2>Compare Students</h2>
                    <form method="get" action="<?= h(currentPageUrl()) ?>" id="compare-form">
                        <div class="form-grid">
                            <div>
                                <label for="quiz_id">Quiz ID</label>
                                <input id="quiz_id" name="quiz_id" inputmode="numeric" value="<?= h($quizIdInput) ?>" placeholder="e.g. 31" autocomplete="off">
                            </div>
                            <div>
                                <label for="student_1">Student 1</label>
                                <select id="student_1" name="student_1">
                                    <option value="">Select a student</option>
                                    <?php foreach ($studentOptions as $option): ?>
                                        <?php
                                        $label = trim($option['full_name']) !== ''
                                            ? $option['full_name'] . ' (' . $option['student_nr'] . ')'
                                            : $option['student_nr'];
                                        if ($option['class'] !== '') {
                                            $label .= ' - ' . $option['class'];
                                        }
                                        ?>
                                        <option value="<?= h($option['student_nr']) ?>" <?= $student1Input === $option['student_nr'] ? 'selected' : '' ?>>
                                            <?= h($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="student_2">Student 2</label>
                                <select id="student_2" name="student_2">
                                    <option value="">Select a student</option>
                                    <?php foreach ($studentOptions as $option): ?>
                                        <?php
                                        $label = trim($option['full_name']) !== ''
                                            ? $option['full_name'] . ' (' . $option['student_nr'] . ')'
                                            : $option['student_nr'];
                                        if ($option['class'] !== '') {
                                            $label .= ' - ' . $option['class'];
                                        }
                                        ?>
                                        <option value="<?= h($option['student_nr']) ?>" <?= $student2Input === $option['student_nr'] ? 'selected' : '' ?>>
                                            <?= h($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="close_threshold">All-Pairs Threshold</label>
                                <select id="close_threshold" name="close_threshold">
                                    <?php foreach ($thresholdOptions as $thresholdOption): ?>
                                        <option value="<?= h((string)$thresholdOption) ?>" <?= $selectedThresholdPercent === $thresholdOption ? 'selected' : '' ?>>
                                            <?= h((string)$thresholdOption) ?>%
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="actions">
                            <button class="button" type="submit" name="action" value="compare">Compare</button>
                            <button class="button secondary" type="submit" name="action" value="all_pairs">Compare All Students</button>
                            <a class="button secondary" href="<?= h(currentPageUrl()) ?>">Reset</a>
                            <?php if ($hasQuizLookup && $studentOptions === [] && $errors === [] && $connectionError === null && $queryError === null): ?>
                                <span class="muted">No students found yet for this quiz.</span>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <?php if ($errors !== [] || $connectionError !== null || $queryError !== null): ?>
                    <section class="messages">
                        <?php foreach ($errors as $error): ?>
                            <div class="message error"><?= h($error) ?></div>
                        <?php endforeach; ?>
                        <?php if ($connectionError !== null): ?>
                            <div class="message error">Database connection failed: <?= h($connectionError) ?></div>
                        <?php endif; ?>
                        <?php if ($queryError !== null): ?>
                            <div class="message error">Query failed: <?= h($queryError) ?></div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ($submitted && $errors === [] && $connectionError === null && $queryError === null && $activeAction === 'compare'): ?>
                    <?php
                    $student1Submission = $selectedSubmissions[$student1Input] ?? null;
                    $student2Submission = $selectedSubmissions[$student2Input] ?? null;
                    $student1Header = $student1Submission !== null
                        ? trim((string)$student1Submission['first_name'] . ' ' . (string)$student1Submission['last_name'])
                        : 'Student 1';
                    $student2Header = $student2Submission !== null
                        ? trim((string)$student2Submission['first_name'] . ' ' . (string)$student2Submission['last_name'])
                        : 'Student 2';
                    $dominantLeaderKey = $comparePairSignals['dominant_close_leader'] ?? null;
                    $student1DisplayHeader = $student1Header;
                    $student2DisplayHeader = $student2Header;
                    $student1ColumnKey = 'student_1';
                    $student2ColumnKey = 'student_2';
                    $displayedSubmissions = [$student1Submission, $student2Submission];

                    if ($dominantLeaderKey === 'student_2') {
                        $student1DisplayHeader = $student2Header;
                        $student2DisplayHeader = $student1Header;
                        $student1ColumnKey = 'student_2';
                        $student2ColumnKey = 'student_1';
                        $displayedSubmissions = [$student2Submission, $student1Submission];
                    }
                    ?>

                    <section class="card">
                        <h2>Selected Attempts</h2>
                        <p class="muted">
                            <?= $quiz !== null ? 'Quiz ' . h((string)$quiz['id']) . ': ' . h((string)$quiz['name']) : 'Quiz not found in quiz table.' ?>
                        </p>

                        <?php if ($student1Submission === null || $student2Submission === null): ?>
                            <div class="messages">
                                <?php if ($student1Submission === null): ?>
                                    <div class="message warn">No submission found for student <?= h($student1Input) ?> in quiz <?= h($quizIdInput) ?>.</div>
                                <?php endif; ?>
                                <?php if ($student2Submission === null): ?>
                                    <div class="message warn">No submission found for student <?= h($student2Input) ?> in quiz <?= h($quizIdInput) ?>.</div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="meta-grid">
                                <?php foreach ($displayedSubmissions as $submission): ?>
                                    <article class="submission-card">
                                        <span class="eyebrow">Student <?= h((string)$submission['student_nr']) ?></span>
                                        <strong><?= h(trim((string)$submission['first_name'] . ' ' . (string)$submission['last_name'])) ?></strong>
                                        <dl>
                                            <dt>Submission ID</dt>
                                            <dd><?= h((string)$submission['id']) ?></dd>
                                            <dt>Class</dt>
                                            <dd><?= h((string)$submission['class']) ?></dd>
                                            <dt>Started</dt>
                                            <dd><?= h(formatTimestamp($submission['start_time'] ?? null)) ?></dd>
                                            <dt>Finished</dt>
                                            <dd><?= h(formatTimestamp($submission['end_time'] ?? null)) ?></dd>
                                            <dt>Answered</dt>
                                            <dd><?= h((string)$submission['no_answered']) ?> / <?= h((string)$submission['no_questions']) ?></dd>
                                            <dt>Correct</dt>
                                            <dd><?= h((string)$submission['no_correct']) ?></dd>
                                        </dl>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <?php if ($student1Submission !== null && $student2Submission !== null): ?>
                        <section class="card">
                            <h2>Summary</h2>
                            <div class="summary-grid">
                                <div class="metric">
                                    <span class="eyebrow">Matched Questions</span>
                                    <strong><?= h((string)($summary['matched_questions'] ?? 0)) ?></strong>
                                </div>
                                <div class="metric">
                                    <span class="eyebrow">Close Rows</span>
                                    <strong><?= h((string)($summary['close_rows'] ?? 0)) ?></strong>
                                </div>
                                <div class="metric">
                                    <span class="eyebrow">Smallest Diff</span>
                                    <strong><?= h(($summary['smallest_diff'] ?? null) !== null ? (string)$summary['smallest_diff'] . 's' : '—') ?></strong>
                                </div>
                                <div class="metric">
                                    <span class="eyebrow">Average Diff</span>
                                    <strong><?= h(($summary['average_diff'] ?? null) !== null ? number_format((float)$summary['average_diff'], 1) . 's' : '—') ?></strong>
                                </div>
                            </div>
                        </section>

                        <section class="card">
                            <h2>Per-Question Comparison</h2>
                            <?php if ($comparisonRows === []): ?>
                                <div class="message warn">No answer log rows were found for the selected submissions.</div>
                            <?php else: ?>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Question</th>
                                                <th><?= h($student1DisplayHeader) ?></th>
                                                <th><?= h($student2DisplayHeader) ?></th>
                                                <th>Diff</th>
                                                <th>Flag</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($comparisonRows as $row): ?>
                                                <?php
                                                $left = $row[$student1ColumnKey];
                                                $right = $row[$student2ColumnKey];
                                                $isMissing = $left === null || $right === null;
                                                $rowClass = $row['is_close'] ? 'close-row' : ($isMissing ? 'missing-row' : '');
                                                $leftIsEarlier = $left !== null
                                                    && $right !== null
                                                    && $left['timestamp'] !== null
                                                    && $right['timestamp'] !== null
                                                    && strtotime($left['timestamp']) < strtotime($right['timestamp']);
                                                $rightIsEarlier = $left !== null
                                                    && $right !== null
                                                    && $left['timestamp'] !== null
                                                    && $right['timestamp'] !== null
                                                    && strtotime($right['timestamp']) < strtotime($left['timestamp']);
                                                $signedDiffSeconds = null;
                                                if (is_int($row['diff_seconds'] ?? null)) {
                                                    if ($leftIsEarlier) {
                                                        $signedDiffSeconds = (int)$row['diff_seconds'];
                                                    } elseif ($rightIsEarlier) {
                                                        $signedDiffSeconds = -((int)$row['diff_seconds']);
                                                    } else {
                                                        $signedDiffSeconds = 0;
                                                    }
                                                }
                                                $leftCorrectText = $left !== null ? formatBool($left['correct']) : '—';
                                                $leftCorrectClass = $leftCorrectText === 'Yes'
                                                    ? 'yes'
                                                    : ($leftCorrectText === 'No' ? 'no' : '');
                                                $rightCorrectText = $right !== null ? formatBool($right['correct']) : '—';
                                                $rightCorrectClass = $rightCorrectText === 'Yes'
                                                    ? 'yes'
                                                    : ($rightCorrectText === 'No' ? 'no' : '');
                                                $sameAnswer = $left !== null
                                                    && $right !== null
                                                    && ($left['answer_no'] ?? null) !== null
                                                    && ($right['answer_no'] ?? null) !== null
                                                    && (string)$left['answer_no'] === (string)$right['answer_no'];
                                                $sameWrongAnswer = $sameAnswer
                                                    && (($left['correct'] ?? null) === 0)
                                                    && (($right['correct'] ?? null) === 0);
                                                $wrongAnswerShareSuffix = '';
                                                if ($row['is_close'] && $sameWrongAnswer) {
                                                    $questionId = (int)$row['question_id'];
                                                    $sharedAnswerNo = $left['answer_no'] ?? null;
                                                    $answerCount = $sharedAnswerNo !== null
                                                        ? ($answerStatsByQuestion[$questionId]['answers'][$sharedAnswerNo] ?? null)
                                                        : null;
                                                    $answerTotal = $answerStatsByQuestion[$questionId]['wrong_total'] ?? null;
                                                    $wrongAnswerShareSuffix = formatInlineAnswerShare(
                                                        is_int($answerCount) ? $answerCount : null,
                                                        is_int($answerTotal) ? $answerTotal : null
                                                    );
                                                }
                                                ?>
                                                <tr class="<?= h($rowClass) ?>">
                                                    <td>
                                                        <?php if ($row['label'] !== ''): ?>
                                                            <span class="question-label"><?= h($row['label']) ?></span>
                                                        <?php endif; ?>
                                                        <div><strong>Question ID <?= h((string)$row['question_id']) ?></strong></div>
                                                        <div class="question-preview">
                                                            <?= h($row['question'] !== '' ? mb_strimwidth($row['question'], 0, 140, '...') : 'Question text unavailable.') ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($left === null): ?>
                                                            <span class="muted">No answer submitted</span>
                                                        <?php else: ?>
                                                            <div class="cell-stack<?= $leftIsEarlier ? ' earliest' : '' ?>">
                                                                <div><strong>Q# <?= h((string)$left['question_no']) ?></strong></div>
                                                                <small>Answer: <?= h((string)$left['answer_no']) ?><?= h($wrongAnswerShareSuffix) ?></small>
                                                                <small>Correct: <span class="correct-status <?= h($leftCorrectClass) ?>"><?= h($leftCorrectText) ?></span></small>
                                                                <small class="<?= $leftIsEarlier ? 'earliest-timestamp' : '' ?>"><?= h(formatTimestamp($left['timestamp'])) ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($right === null): ?>
                                                            <span class="muted">No answer submitted</span>
                                                        <?php else: ?>
                                                            <div class="cell-stack<?= $rightIsEarlier ? ' earliest' : '' ?>">
                                                                <div><strong>Q# <?= h((string)$right['question_no']) ?></strong></div>
                                                                <small>Answer: <?= h((string)$right['answer_no']) ?><?= h($wrongAnswerShareSuffix) ?></small>
                                                                <small>Correct: <span class="correct-status <?= h($rightCorrectClass) ?>"><?= h($rightCorrectText) ?></span></small>
                                                                <small class="<?= $rightIsEarlier ? 'earliest-timestamp' : '' ?>"><?= h(formatTimestamp($right['timestamp'])) ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= h(formatSignedSeconds($signedDiffSeconds)) ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($row['diff_seconds'] === null): ?>
                                                            <span class="flag missing">Missing answer</span>
                                                        <?php elseif ($row['is_close'] && $sameWrongAnswer): ?>
                                                            <span class="flag close-same-wrong">Close + Same</span>
                                                        <?php elseif ($row['is_close'] && $sameAnswer): ?>
                                                            <span class="flag close-same">Close + Same</span>
                                                        <?php elseif ($row['is_close']): ?>
                                                            <span class="flag close">Close</span>
                                                        <?php else: ?>
                                                            <span class="muted">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </section>

                        <?php if ($comparePairSignals !== null): ?>
                            <section class="card">
                                <h2>Fraud Signals</h2>
                                <p class="muted">
                                    This repeats the same pair-level summary used in the overview. It combines the direct timing result for this pair with quiz-wide baseline and permutation comparisons, so you can inspect the detailed question table and the high-level indicators on one page.
                                </p>
                                <div class="summary-grid">
                                    <div class="metric">
                                        <span class="eyebrow">Matched Questions</span>
                                        <strong><?= h((string)$comparePairSignals['matched_questions']) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Close Rows</span>
                                        <strong><?= h((string)$comparePairSignals['close_rows']) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Expected Close %</span>
                                        <strong><?= h(formatPercent(isset($comparePairSignals['expected_close_ratio']) ? (float)$comparePairSignals['expected_close_ratio'] : null)) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Observed Close %</span>
                                        <strong><?= h(formatPercent((float)$comparePairSignals['close_ratio'])) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Longest Close Run</span>
                                        <strong><?= h((string)$comparePairSignals['longest_close_streak']) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Dominant Leader</span>
                                        <strong><?php
                                            if (($comparePairSignals['dominant_close_leader'] ?? null) === 'student_1') {
                                                echo h($student1Header);
                                            } elseif (($comparePairSignals['dominant_close_leader'] ?? null) === 'student_2') {
                                                echo h($student2Header);
                                            } elseif (($comparePairSignals['dominant_close_leader'] ?? null) === 'tie') {
                                                echo h('Mixed');
                                            } else {
                                                echo h('—');
                                            }
                                        ?></strong>
                                        <small class="muted"><?php
                                            $dominantLeadCount = isset($comparePairSignals['dominant_close_lead_count'])
                                                ? (int)$comparePairSignals['dominant_close_lead_count']
                                                : 0;
                                            $closeRowsWithLeader = isset($comparePairSignals['close_rows_with_leader'])
                                                ? (int)$comparePairSignals['close_rows_with_leader']
                                                : 0;
                                            $tiedCloseRows = max(0, (int)$comparePairSignals['close_rows'] - $closeRowsWithLeader);

                                            if (($comparePairSignals['dominant_close_leader'] ?? null) === 'tie') {
                                                echo h($dominantLeadCount . ' vs ' . $dominantLeadCount . ' close-row leads');
                                            } elseif (($comparePairSignals['dominant_close_leader'] ?? null) === 'student_1' || ($comparePairSignals['dominant_close_leader'] ?? null) === 'student_2') {
                                                $detail = $dominantLeadCount . ' of ' . $closeRowsWithLeader . ' non-tied close rows';
                                                if ($tiedCloseRows > 0) {
                                                    $detail .= ' (' . $tiedCloseRows . ' tied)';
                                                }
                                                echo h($detail);
                                            } else {
                                                echo h('No non-tied close rows');
                                            }
                                        ?></small>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Leader Ahead Consistency</span>
                                        <strong><?= h(formatPercent(isset($comparePairSignals['dominant_leader_ratio']) ? (float)$comparePairSignals['dominant_leader_ratio'] : null)) ?></strong>
                                        <small class="muted"><?php
                                            if (($comparePairSignals['dominant_close_leader'] ?? null) === 'student_1' || ($comparePairSignals['dominant_close_leader'] ?? null) === 'student_2') {
                                                echo h($dominantLeadCount . ' of ' . $closeRowsWithLeader . ' non-tied close rows');
                                            } else {
                                                echo h('No non-tied close rows');
                                            }
                                        ?></small>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Longest Same-Leader Run</span>
                                        <strong><?= h((string)($comparePairSignals['longest_same_leader_close_run'] ?? 0)) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Copied Answers From Leader</span>
                                        <strong><?= h(formatCountAndPercent(
                                            isset($comparePairSignals['dominant_leader_same_answer_count']) ? (int)$comparePairSignals['dominant_leader_same_answer_count'] : null,
                                            isset($comparePairSignals['dominant_close_lead_count']) ? (int)$comparePairSignals['dominant_close_lead_count'] : null
                                        )) ?></strong>
                                        <small class="muted"><?php
                                            $sameAnswerCount = isset($comparePairSignals['dominant_leader_same_answer_count'])
                                                ? (int)$comparePairSignals['dominant_leader_same_answer_count']
                                                : 0;
                                            if (($comparePairSignals['dominant_close_leader'] ?? null) === 'student_1' || ($comparePairSignals['dominant_close_leader'] ?? null) === 'student_2') {
                                                echo h($sameAnswerCount . ' of ' . $dominantLeadCount . ' leader-led close rows');
                                            } else {
                                                echo h('No leader-led close rows');
                                            }
                                        ?></small>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Copied Wrong Answers</span>
                                        <strong><?= h(formatCountAndPercent(
                                            isset($comparePairSignals['dominant_leader_same_wrong_answer_count']) ? (int)$comparePairSignals['dominant_leader_same_wrong_answer_count'] : null,
                                            isset($comparePairSignals['close_rows_with_wrong_answer']) ? (int)$comparePairSignals['close_rows_with_wrong_answer'] : null
                                        )) ?></strong>
                                        <small class="muted"><?php
                                            $sameWrongAnswerCount = isset($comparePairSignals['dominant_leader_same_wrong_answer_count'])
                                                ? (int)$comparePairSignals['dominant_leader_same_wrong_answer_count']
                                                : 0;
                                            $closeRowsWithWrongAnswer = isset($comparePairSignals['close_rows_with_wrong_answer'])
                                                ? (int)$comparePairSignals['close_rows_with_wrong_answer']
                                                : 0;
                                            if ($closeRowsWithWrongAnswer > 0) {
                                                echo h($sameWrongAnswerCount . ' of ' . $closeRowsWithWrongAnswer . ' close rows with a wrong answer');
                                            } else {
                                                echo h('No close rows with a wrong answer');
                                            }
                                        ?></small>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Perm. p</span>
                                        <strong><?= h(formatPValue($comparePairSignals['permutation_p_value'] !== null ? (float)$comparePairSignals['permutation_p_value'] : null)) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Rarity 1:N</span>
                                        <strong><?= h(formatOneInOdds($comparePairSignals['rarity_one_in'] !== null ? (float)$comparePairSignals['rarity_one_in'] : null)) ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Smallest Diff</span>
                                        <strong><?= h($comparePairSignals['smallest_diff'] !== null ? (string)$comparePairSignals['smallest_diff'] . 's' : '—') ?></strong>
                                    </div>
                                    <div class="metric">
                                        <span class="eyebrow">Average Diff</span>
                                        <strong><?= h($comparePairSignals['average_diff'] !== null ? number_format((float)$comparePairSignals['average_diff'], 1) . 's' : '—') ?></strong>
                                    </div>
                                </div>
                                <p class="muted">
                                    <strong>Perm. p</strong> shows how often a result this unusual would happen by chance if students were paired randomly in the same quiz. It now compares not only how many close rows the pair has, but also how consistently the same student stays ahead during close rows. A smaller value means the pair stands out more.
                                </p>
                                <p class="muted">
                                    <strong>Rarity 1:N</strong> Rarity score estimates how rare this level of closeness is under the timing baseline model according to a binomial distribution.
                                </p>
            
                            </section>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($submitted && $errors === [] && $connectionError === null && $queryError === null && $activeAction === 'all_pairs'): ?>
                    <section class="card">
                        <h2>Timing Stats</h2>
                        <p class="muted">
                            Based on all answered questions in the latest submission per student for this quiz.
                        </p>
                        <div class="summary-grid">
                            <div class="metric">
                                <span class="eyebrow">Answered Questions</span>
                                <strong><?= h((string)($durationStats['count'] ?? 0)) ?></strong>
                            </div>
                            <div class="metric">
                                <span class="eyebrow">Average Answer Time</span>
                                <strong><?= h(formatDuration(isset($durationStats['average_seconds']) ? (float)$durationStats['average_seconds'] : null)) ?></strong>
                            </div>
                            <div class="metric">
                                <span class="eyebrow">Std Dev Answer Time</span>
                                <strong><?= h(formatDuration(isset($durationStats['stdev_seconds']) ? (float)$durationStats['stdev_seconds'] : null)) ?></strong>
                            </div>
                            <div class="metric">
                                <span class="eyebrow">Average Seconds</span>
                                <strong><?= h(isset($durationStats['average_seconds']) && $durationStats['average_seconds'] !== null ? number_format((float)$durationStats['average_seconds'], 1) . 's' : '—') ?></strong>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <h2>Quiz Baseline</h2>
                        <p class="muted">
                            Empirical probability that any two students answer the same matched question within <?= h((string)SUSPICIOUS_SECONDS) ?> seconds, based on all student pairs in this quiz.
                        </p>
                        <div class="summary-grid">
                            <div class="metric">
                                <span class="eyebrow">Pairs Compared</span>
                                <strong><?= h((string)($baselineStats['pairs_compared'] ?? 0)) ?></strong>
                            </div>
                            <div class="metric">
                                <span class="eyebrow">Matched Answer Pairs</span>
                                <strong><?= h((string)($baselineStats['matched_questions'] ?? 0)) ?></strong>
                            </div>
                            <div class="metric">
                                <span class="eyebrow">Close Answer Pairs</span>
                                <strong><?= h((string)($baselineStats['close_rows'] ?? 0)) ?></strong>
                            </div>
                            <div class="metric">
                                <span class="eyebrow">P(Within <?= h((string)SUSPICIOUS_SECONDS) ?>s)</span>
                                <strong><?= h(isset($baselineStats['close_probability']) && $baselineStats['close_probability'] !== null ? number_format((float)$baselineStats['close_probability'] * 100, 2) . '%' : '—') ?></strong>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <h2>Potential Fraud Couples</h2>
                        <p class="muted">
                            Students are listed here when more than <?= h((string)$selectedThresholdPercent) ?>% of their matched answer rows were flagged close within <?= h((string)SUSPICIOUS_SECONDS) ?> seconds.
                        </p>

                        <?php if ($potentialFraudPairs === []): ?>
                            <div class="message warn">No potential fraud couples found for this quiz using the current threshold.</div>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table class="pair-table">
                                    <thead>
                                        <tr>
                                            <th>Student 1</th>
                                            <th>Student 2</th>
                                            <th>Matched Questions</th>
                                            <th>Close Rows</th>
                                            <th>Close %</th>
                                            <th>
                                                <span
                                                    class="help-label"
                                                    tabindex="0"
                                                    data-help="<?= h('The p-value shows how often a result this unusual would appear just by chance if students were paired randomly. Here it is estimated using ' . number_format(PERMUTATION_RUNS, 0, '.', ',') . ' simulated reshuffles of student pairings within the same quiz. It now considers both how many close rows the pair has and how consistently the same student stays ahead on those close rows. A smaller p-value means the pair stands out more.') ?>"
                                                >
                                                    Perm. p
                                                </span>
                                            </th>
                                            <th>
                                                <span
                                                    class="help-label"
                                                    tabindex="0"
                                                    data-help="Baseline rarity under the quiz model. Calculated as 1 divided by the binomial tail probability P(X >= close rows), with X ~ Binomial(matched questions, quiz baseline P(within 15s)). This is a rarity score under the baseline model."
                                                >
                                                    Rarity 1:N
                                                </span>
                                            </th>
                                            <th>Smallest Diff</th>
                                            <th>Average Diff</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($potentialFraudPairs as $pair): ?>
                                            <?php
                                            $leftName = trim((string)$pair['student_1']['first_name'] . ' ' . (string)$pair['student_1']['last_name']);
                                            $rightName = trim((string)$pair['student_2']['first_name'] . ' ' . (string)$pair['student_2']['last_name']);
                                            $pairSummary = $pair['summary'];
                                            $sameWindowChance = $pair['same_window_chance'] ?? null;
                                            $permutationPValue = $pair['permutation_p_value'] ?? null;
                                            $compareUrl = currentPageUrl()
                                                . '?quiz_id=' . rawurlencode($quizIdInput)
                                                . '&student_1=' . rawurlencode((string)$pair['student_1']['student_nr'])
                                                . '&student_2=' . rawurlencode((string)$pair['student_2']['student_nr'])
                                                . '&action=compare';
                                            ?>
                                            <tr class="close-row clickable-row" onclick="window.location.href='<?= h($compareUrl) ?>';">
                                                <td>
                                                    <a class="pair-link-row" href="<?= h($compareUrl) ?>">
                                                        <div class="cell-stack">
                                                            <div><strong><?= h($leftName) ?></strong></div>
                                                            <small><?= h((string)$pair['student_1']['student_nr']) ?><?= $pair['student_1']['class'] !== '' ? ' - ' . h((string)$pair['student_1']['class']) : '' ?></small>
                                                            <small>Submission <?= h((string)$pair['student_1']['id']) ?></small>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a class="pair-link-row" href="<?= h($compareUrl) ?>">
                                                        <div class="cell-stack">
                                                            <div><strong><?= h($rightName) ?></strong></div>
                                                            <small><?= h((string)$pair['student_2']['student_nr']) ?><?= $pair['student_2']['class'] !== '' ? ' - ' . h((string)$pair['student_2']['class']) : '' ?></small>
                                                            <small>Submission <?= h((string)$pair['student_2']['id']) ?></small>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h((string)$pairSummary['matched_questions']) ?></a></td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h((string)$pairSummary['close_rows']) ?></a></td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h(formatPercent((float)$pairSummary['close_ratio'])) ?></a></td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h(formatPValue($permutationPValue !== null ? (float)$permutationPValue : null)) ?></a></td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h(formatOneInOdds($sameWindowChance !== null ? (float)$sameWindowChance : null)) ?></a></td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h($pairSummary['smallest_diff'] !== null ? (string)$pairSummary['smallest_diff'] . 's' : '—') ?></a></td>
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h($pairSummary['average_diff'] !== null ? number_format((float)$pairSummary['average_diff'], 1) . 's' : '—') ?></a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
        (function () {
            var form = document.getElementById('compare-form');
            var quizInput = document.getElementById('quiz_id');
            var student1 = document.getElementById('student_1');
            var student2 = document.getElementById('student_2');

            if (!form || !quizInput) {
                return;
            }

            var initialQuizId = quizInput.value.trim();

            function submitForQuizLookup() {
                var nextQuizId = quizInput.value.trim();
                if (nextQuizId === '' || nextQuizId === initialQuizId) {
                    return;
                }

                if (student1) {
                    student1.value = '';
                }
                if (student2) {
                    student2.value = '';
                }

                form.submit();
            }

            quizInput.addEventListener('blur', submitForQuizLookup);
            quizInput.addEventListener('change', submitForQuizLookup);
        }());
    </script>
</body>
</html>
