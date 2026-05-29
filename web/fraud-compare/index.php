<?php
declare(strict_types=1);

const SUSPICIOUS_SECONDS = 15;

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
        return '1:' . number_format($inverse, 1);
    }

    return '1:' . sprintf('%.2e', $inverse);
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
            $diffSeconds = abs(strtotime($left['timestamp']) - strtotime($right['timestamp']));
        }

        $comparisonRows[] = [
            'question_id' => $questionId,
            'label' => $questionMeta[$questionId]['label'] ?? '',
            'question' => $questionMeta[$questionId]['question'] ?? '',
            'student_1' => $left,
            'student_2' => $right,
            'diff_seconds' => $diffSeconds,
            'is_close' => $diffSeconds !== null && $diffSeconds <= SUSPICIOUS_SECONDS,
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
 * @return array{matched_questions:int, close_rows:int, smallest_diff:?int, average_diff:?float, close_ratio:float}
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

    return [
        'matched_questions' => $matchedCount,
        'close_rows' => $closeCount,
        'smallest_diff' => $diffValues !== [] ? min($diffValues) : null,
        'average_diff' => $diffValues !== [] ? (array_sum($diffValues) / count($diffValues)) : null,
        'close_ratio' => $matchedCount > 0 ? $closeCount / $matchedCount : 0.0,
    ];
}

/**
 * @param array<string, mixed> $leftSubmission
 * @param array<string, mixed> $rightSubmission
 * @param array<int, array<int, array{question_id:int, question_no:?int, answer_no:?int, correct:?int, timestamp:?string}>> $logsBySubmissionId
 * @param array<int, array{label: string, question: string}> $questionMeta
 * @return array{rows: array<int, array<string, mixed>>, summary: array{matched_questions:int, close_rows:int, smallest_diff:?int, average_diff:?float, close_ratio:float}}
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
$comparisonRows = [];
$summary = null;
$potentialFraudPairs = [];
$durationStats = null;
$baselineStats = null;

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
                    $logsBySubmissionId[$submissionId][$questionId] = [
                        'question_id' => $questionId,
                        'question_no' => isset($logRow['no_answered']) ? ((int)$logRow['no_answered'] + 1) : null,
                        'answer_no' => isset($logRow['answer_no']) ? (int)$logRow['answer_no'] : null,
                        'correct' => isset($logRow['correct']) ? (int)$logRow['correct'] : null,
                        'timestamp' => $logRow['timestamp'] ?? null,
                    ];

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
                    for ($leftIndex = 0; $leftIndex < $studentCount - 1; $leftIndex++) {
                        for ($rightIndex = $leftIndex + 1; $rightIndex < $studentCount; $rightIndex++) {
                            $leftSubmission = $students[$leftIndex];
                            $rightSubmission = $students[$rightIndex];
                            $pairAnalysis = analyzeSubmissionPair($leftSubmission, $rightSubmission, $logsBySubmissionId, $questionMeta);
                            $pairSummary = $pairAnalysis['summary'];
                            if ($pairSummary['matched_questions'] > 0) {
                                $baselinePairsCompared++;
                                $baselineMatchedQuestions += $pairSummary['matched_questions'];
                                $baselineCloseRows += $pairSummary['close_rows'];
                            }

                            if ($pairSummary['matched_questions'] > 0 && $pairSummary['close_ratio'] > 0.5) {
                                $sameWindowChance = null;
                                if ($baselineMatchedQuestions > 0) {
                                    $baselineProbability = $baselineCloseRows / $baselineMatchedQuestions;
                                    $sameWindowChance = $baselineProbability > 0.0
                                        ? pow($baselineProbability, $pairSummary['close_rows'])
                                        : 0.0;
                                }

                                $potentialFraudPairs[] = [
                                    'student_1' => $leftSubmission,
                                    'student_2' => $rightSubmission,
                                    'summary' => $pairSummary,
                                    'same_window_chance' => $sameWindowChance,
                                ];
                            }
                        }
                    }

                    usort($potentialFraudPairs, static function (array $left, array $right): int {
                        $ratioCompare = $right['summary']['close_ratio'] <=> $left['summary']['close_ratio'];
                        if ($ratioCompare !== 0) {
                            return $ratioCompare;
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
            background: var(--warn-soft);
            color: var(--warn);
        }

        .flag.ok {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .flag.missing {
            background: #ebe6dd;
            color: var(--muted);
        }

        .muted {
            color: var(--muted);
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
                                <?php foreach ([$student1Submission, $student2Submission] as $submission): ?>
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
                                                <th><?= h($student1Header) ?></th>
                                                <th><?= h($student2Header) ?></th>
                                                <th>Diff</th>
                                                <th>Flag</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($comparisonRows as $row): ?>
                                                <?php
                                                $left = $row['student_1'];
                                                $right = $row['student_2'];
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
                                                                <small>Answer: <?= h((string)$left['answer_no']) ?></small>
                                                                <small>Correct: <?= h(formatBool($left['correct'])) ?></small>
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
                                                                <small>Answer: <?= h((string)$right['answer_no']) ?></small>
                                                                <small>Correct: <?= h(formatBool($right['correct'])) ?></small>
                                                                <small class="<?= $rightIsEarlier ? 'earliest-timestamp' : '' ?>"><?= h(formatTimestamp($right['timestamp'])) ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= h($row['diff_seconds'] !== null ? (string)$row['diff_seconds'] . 's' : '—') ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($row['diff_seconds'] === null): ?>
                                                            <span class="flag missing">Missing answer</span>
                                                        <?php elseif ($row['is_close']): ?>
                                                            <span class="flag close">Close</span>
                                                        <?php else: ?>
                                                            <span class="flag ok">OK</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </section>
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
                            Students are listed here when more than 50% of their matched answer rows were flagged close within <?= h((string)SUSPICIOUS_SECONDS) ?> seconds.
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
                                            <th>1:N</th>
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
                                                <td><a class="pair-link-row" href="<?= h($compareUrl) ?>"><?= h(number_format($pairSummary['close_ratio'] * 100, 1)) ?>%</a></td>
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
