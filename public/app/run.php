<?php
declare(strict_types=1);

require_once __DIR__ . '/direct.php';
require_once __DIR__ . '/helpers/getDbVersion.php';
require_once __DIR__ . '/helpers/checkPassdata.php';
// require_once __DIR__ . '/helpers/isset.php'; 
require_once __DIR__ . '/helpers/parseJson.php';
require_once __DIR__ . '/helpers/camelSnakeConverters.php';
require_once __DIR__ . '/security/sessionHandlers.php';

function run(): void
{
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../db/db.sqlite');

        $response = direct($pdo);

        if($response) {
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    } catch (\Throwable $th) {
        http_response_code(500);
        print_r($th);
    } finally {
        $pdo = null;
    }
}