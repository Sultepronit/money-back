<?php
declare(strict_types=1);

$pdo = new PDO('sqlite:' . __DIR__ . '/db.sqlite');

// $dropTalbe = "DROP TABLE IF EXISTS main_table";
// $pdo->exec($dropTalbe);

// $createTable = "CREATE TABLE IF NOT EXISTS main_table (
//     'date' TEXT PRIMARY KEY,
//     vira_black REAL DEFAULT NULL,
//     vira_black_income TEXT DEFAULT NULL,
//     vira_white REAL DEFAULT NULL,
//     vira_white_income TEXT DEFAULT NULL,
//     vira_cash_expense TEXT DEFAULT NULL,
//     vira_cash_income TEXT DEFAULT NULL
// )";

// $pdo->exec($createTable);

// $populate = "INSERT INTO main_table
//     ('date', vira_black, vira_white) VALUES
//     ('2024-05-29', '6785', '433');
// ";
// $pdo->exec($populate);




// $drop = "DROP TABLE IF EXISTS secure";
// $pdo->exec($drop);

// $createSecure = "CREATE TABLE IF NOT EXISTS secure (
//     password TEXT
// )";
// $pdo->exec($createSecure);

// $encr = password_hash('password', PASSWORD_BCRYPT);
// echo $encr;
// $addDummy = "INSERT INTO secure VALUES (?)";
// $stmt = $pdo->prepare($addDummy);
// $stmt->execute([$encr]);




$query = "SELECT * FROM secure";
$data = $pdo->query($query)->fetch(PDO::FETCH_COLUMN);
// print_r($data);
var_dump($data);
var_dump(password_verify('password1', $data));
