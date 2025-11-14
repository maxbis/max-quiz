<?php
declare(strict_types=1);
 
$formatClass = static function (?string $value): string {
    $value = trim((string)$value);
    if ($value === '') {
        return '–';
    }

    $suffix = substr($value, -2);
    return strtoupper($suffix);
};

$dbConfigPath = dirname(__DIR__, 2) . '/config/db.php';
$dbConfig = require $dbConfigPath;

$dsn = (string)($dbConfig['dsn'] ?? '');
$dbUser = (string)($dbConfig['username'] ?? '');
$dbPass = (string)($dbConfig['password'] ?? '');
$connectionError = null;
$activeQuizzes = [];
$quizGroups = [];
$flatQuizzes = [];
$students = [];
$scores = [];
$csvHeaders = [];
$csvRows = [];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $quizStmt = $pdo->query("
        SELECT id, name, no_questions
        FROM quiz
        WHERE active = 1
        ORDER BY name ASC
    ");
    $activeQuizzes = $quizStmt->fetchAll();

    if ($activeQuizzes) {
        foreach ($activeQuizzes as $quiz) {
            $parts = explode('.', $quiz['name'], 2);
            $main = $parts[0];
            $sub = $parts[1] ?? $parts[0];

            $entry = [
                'id' => (int)$quiz['id'],
                'main' => $main,
                'sub' => $sub,
                'full_name' => $quiz['name'],
            ];

            $quizGroups[$main][] = $entry;
            $flatQuizzes[] = $entry;
        }

        $csvHeaders = ['Student', 'Class', 'Name'];
        foreach ($flatQuizzes as $quiz) {
            $csvHeaders[] = $quiz['main'] . ' ' . $quiz['sub'];
        }
        $csvHeaders[] = 'Average';

        $quizIds = array_column($activeQuizzes, 'id');
        $placeholder = implode(',', array_fill(0, count($quizIds), '?'));

        $submissionStmt = $pdo->prepare("
            SELECT
                s.id,
                s.student_nr,
                TRIM(CONCAT(s.first_name, ' ', s.last_name)) AS full_name,
                s.class,
                s.quiz_id,
                s.no_correct,
                s.no_questions,
                COALESCE(s.end_time, s.last_updated) AS sort_time
            FROM submission s
            WHERE s.quiz_id IN ($placeholder)
              AND s.student_nr IS NOT NULL
              AND s.student_nr <> ''
            ORDER BY s.student_nr ASC, s.quiz_id ASC, sort_time DESC, s.id DESC
        ");
        $submissionStmt->execute($quizIds);
        $rows = $submissionStmt->fetchAll();

        foreach ($rows as $row) {
            $studentNr = trim((string)$row['student_nr']);
            $quizId = (int)$row['quiz_id'];

            if (!isset($students[$studentNr])) {
                $students[$studentNr] = [
                    'student_nr' => $studentNr,
                    'full_name' => $row['full_name'] ?: '—',
                    'class' => $formatClass($row['class']),
                ];
            }

            $total = (int)$row['no_questions'];
            $correct = (int)$row['no_correct'];
            $ratio = $total > 0 ? $correct / $total : null;

            if (!isset($scores[$studentNr])) {
                $scores[$studentNr] = [];
            }

            if (!isset($scores[$studentNr][$quizId]) || ($ratio !== null && $ratio > $scores[$studentNr][$quizId]['ratio'])) {
                $scores[$studentNr][$quizId] = [
                    'ratio' => $ratio,
                    'correct' => $correct,
                    'total' => $total,
                ];
            }
        }

        ksort($students, SORT_NATURAL);
    }
} catch (PDOException $exception) {
    $connectionError = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Overview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <header class="app-header">
        <div class="brand">
            <div class="logo">M</div>
            <div class="brand-copy">
                <span class="brand-title">Quiz</span>
                <span class="brand-subtitle">Overview</span>
            </div>
        </div>
        <!-- <nav class="main-nav">
            <a href="#" class="active">Quizzes</a>
            <a href="#">Questions</a>
            <a href="#">Student View</a>
        </nav>
        <button class="logout">Logout (admin)</button> -->
    </header>

    <main class="page">
        <section class="card">
            <div class="card-header">
                <div class="tabs">
                    <button type="button" class="   " onclick="window.history.back();"><< Back</button>
                </div>
                <button type="button" class="export-btn" id="export-btn">Export CSV</button>
            </div>

            <?php if ($connectionError): ?>
                <div class="state state-error">
                    <strong>Database connection failed.</strong>
                    <p><?= htmlspecialchars($connectionError, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php elseif (!$activeQuizzes): ?>
                <div class="state">
                    <strong>No active quizzes</strong>
                    <p>Mark at least one quiz as active to see the overview.</p>
                </div>
            <?php elseif (!$students): ?>
                <div class="state">
                    <strong>No submissions yet</strong>
                    <p>We will show students as soon as the first submission for an active quiz is stored.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                        <tr>
                            <th class="sticky sticky-student single-header" rowspan="2">
                                <button type="button" class="sort-trigger" data-sort-index="0">
                                    Student
                                </button>
                            </th>
                            <th class="sticky sticky-class single-header" rowspan="2">
                                <button type="button" class="sort-trigger" data-sort-index="1">
                                    Class
                                </button>
                            </th>
                            <th class="sticky sticky-name subtle single-header" rowspan="2">
                                <button type="button" class="sort-trigger" data-sort-index="2">
                                    Name
                                </button>
                            </th>
                            <?php foreach ($quizGroups as $groupName => $groupQuizzes): ?>
                                <th class="group-header" colspan="<?= count($groupQuizzes) ?>">
                                    <?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="sticky-average single-header" rowspan="2">
                                <button
                                    type="button"
                                    class="sort-trigger"
                                    data-sort-index="<?= 3 + count($flatQuizzes) ?>"
                                >
                                    Average
                                </button>
                            </th>
                        </tr>
                        <tr>
                            <?php $columnIndex = 3; ?>
                            <?php foreach ($quizGroups as $groupQuizzes): ?>
                                <?php foreach ($groupQuizzes as $quiz): ?>
                                    <th class="sub-header" title="<?= htmlspecialchars($quiz['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button
                                            type="button"
                                            class="sort-trigger"
                                            data-sort-index="<?= $columnIndex ?>"
                                        >
                                            <?= htmlspecialchars($quiz['sub'], ENT_QUOTES, 'UTF-8') ?>
                                        </button>
                                    </th>
                                    <?php $columnIndex++; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody id="student-table">
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="sticky sticky-student strong">
                                    <span class="student-nr"><?= htmlspecialchars($student['student_nr'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="sticky sticky-class">
                                    <span class="student-class"><?= htmlspecialchars($student['class'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="sticky sticky-name subtle"><?= htmlspecialchars($student['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <?php
                                $ratioSum = 0;
                                $ratioCount = 0;
                                $rowValues = [
                                    $student['student_nr'],
                                    $student['class'],
                                    $student['full_name'],
                                ];
                                ?>
                                <?php foreach ($flatQuizzes as $quiz): ?>
                                    <?php
                                    $quizId = $quiz['id'];
                                    $cell = $scores[$student['student_nr']][$quizId] ?? null;
                                    $ratio = $cell['ratio'] ?? null;
                                    $percent = $ratio !== null ? round($ratio * 100) : null;
                                    $cellLabel = $percent !== null ? $percent . '%' : '–';
                                    $tone = 'empty';
                                    if ($percent !== null) {
                                        if ($percent >= 80) {
                                            $tone = 'pass';
                                        } elseif ($percent >= 50) {
                                            $tone = 'warn';
                                        } else {
                                            $tone = 'fail';
                                        }
                                        $ratioSum += $ratio;
                                        $ratioCount++;
                                    }
                                    $rowValues[] = $percent !== null ? $percent : '';
                                    ?>
                                    <td class="score <?= $tone ?>">
                                        <span><?= $cellLabel ?></span>
                                    </td>
                                <?php endforeach; ?>
                                <?php
                                $averageRatio = $ratioCount > 0 ? $ratioSum / $ratioCount : null;
                                $averagePercent = $averageRatio !== null ? round($averageRatio * 100) : null;
                                $averageTone = 'empty';
                                if ($averagePercent !== null) {
                                    if ($averagePercent >= 80) {
                                        $averageTone = 'pass';
                                    } elseif ($averagePercent >= 50) {
                                        $averageTone = 'warn';
                                    } else {
                                        $averageTone = 'fail';
                                    }
                                }
                                ?>
                                <td class="score average-cell <?= $averageTone ?>">
                                    <span><?= $averagePercent !== null ? $averagePercent . '%' : '–' ?></span>
                                </td>
                                <?php
                                $rowValues[] = $averagePercent !== null ? $averagePercent : '';
                                $csvRows[] = $rowValues;
                                ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        window.quizExport = <?= json_encode(
            ['headers' => $csvHeaders, 'rows' => $csvRows],
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?>;
    </script>
    <script src="assets/app.js" defer></script>
</body>
</html>

