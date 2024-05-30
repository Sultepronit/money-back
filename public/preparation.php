<?php
declare(strict_types=1);

$pdo = new PDO('sqlite:' . __DIR__ . '/db.sqlite');

$createTable = "CREATE TABLE IF NOT EXISTS main_table (
    'date' TEXT PRIMARY KEY,
    vira_black REAL DEFAULT NULL,
    vira_black_income TEXT DEFAULT NULL,
    vira_white REAL DEFAULT NULL,
    vira_white_income TEXT DEFAULT NULL,
    vira_cach_expence TEXT DEFAULT NULL,
    vira_cach_income TEXT DEFAULT NULL
)";

// $pdo->exec($createTable);

$populate = "INSERT INTO main_table
    ('date', vira_black, vira_white) VALUES
    ('2024-05-29', '6785', '433');
";
$pdo->exec($populate);