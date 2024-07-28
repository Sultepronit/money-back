<?php
declare(strict_types=1);

require_once __DIR__ . '/direct.php';
require_once __DIR__ . '/helpers/getDbVersion.php';
require_once __DIR__ . '/helpers/checkPassdata.php';

function run(): void
{
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../db/db.sqlite');

        $response = direct($pdo);

        if($response) {
            header('Content-Type: application/json');
            echo json_encode($response);
            // print_r($_COOKIE);
        }
    } catch (\Throwable $th) {
        http_response_code(500);
        print_r($th);
    } finally {
        $pdo = null;
    }
}