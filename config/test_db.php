<?php
$db = require __DIR__ . '/db.php';

$dsnOverride = getenv('TEST_DB_DSN');
if ($dsnOverride !== false && $dsnOverride !== '') {
    $db['dsn'] = $dsnOverride;
} else {
    // test database! Important not to run tests on production or development databases
    $db['dsn'] = 'mysql:host=localhost;dbname=yii2basic_test';
}

return $db;
