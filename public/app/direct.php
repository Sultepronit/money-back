<?php
declare(strict_types=1);

require_once __DIR__ . '/Data.php';
require_once __DIR__ . '/update.php';

function parseRequest(): array
{
    $method = $_SERVER['REQUEST_METHOD'];
    $subject = explode('/', $_SERVER['REQUEST_URI'])[2];

    return compact('method', 'subject');
}

function direct(PDO $pdo): ?array
{
    $request = parseRequest();
    
    if($request['subject'] === 'data') {
        return Data::run($pdo);
    } else if($request['method'] === 'PATCH') {
        return update($pdo);
    }

    http_response_code(404);
    echo '<h2>You shall not pass!</h2><h1>🧙🏻‍♂️</h1>';
    return null;
}