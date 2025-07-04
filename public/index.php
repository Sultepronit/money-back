<?php
declare(strict_types=1);

require_once __DIR__ . '/app/run.php';

header('Access-Control-Allow-Origin: *');
// $origins = getenv('CORS_ORIGINS');
// header("Access-Control-Allow-Origin: {$origins}");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, GET, PUT, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

run();