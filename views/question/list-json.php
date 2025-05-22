<?php
/**
 * @var array $questions
 */

header('Content-Type: application/json; charset=utf-8');

// Convert to UTF-8 if needed
array_walk_recursive($questions, function (&$item) {
    if (is_string($item)) {
        $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1');
    }
});

echo json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;