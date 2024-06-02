<?php
declare(strict_types=1);

require_once __DIR__ . '/Receive.php';

function run(): void
{
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../db.sqlite');

        Receive::run($pdo);

    } catch (\Throwable $th) {
        http_response_code(500);
        print_r($th);
    } finally {
        $pdo = null;
    }
}