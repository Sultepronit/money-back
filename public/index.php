<?php
declare(strict_types=1);

require_once __DIR__ . '/app/run.php';

header('Access-Control-Allow-Origin: *');
$origins = getenv('CORS_ORIGINS');
// header("Access-Control-Allow-Origin: {$origins}");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type');

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

run();