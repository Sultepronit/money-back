<?php
declare(strict_types=1);

require_once __DIR__ . '/Data.php';
require_once __DIR__ . '/update.php';
require_once __DIR__ . '/currencyRate/getUsdRate.php';
require_once __DIR__ . '/currencyRate/getEurRate.php';

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
        return Data::receive($pdo);
    } /* else if($request['subject'] === 'data-new') {
        return Data::receiveNew($pdo);
    } else if($request['subject'] === 'login-update') {
        return Data::loginUpdate($pdo);
    } */ else if($request['subject'] === 'refresh') {
        return Data::refresh($pdo);
    } else if ($request['subject'] === 'login-new') {
        return Data::login($pdo);
    } else if($request['subject'] === 'refresh-new') {
        return Data::refreshNew($pdo);
    } else if($request['method'] === 'PATCH') {
        return update($pdo, $request['subject']);
    } else if($request['subject'] === 'usd-rate') {
        return getUsdRate();
    } else if($request['subject'] === 'eur-rate') {
        return getEurRate();
    } else if($request['subject'] === 'wait-debit') {
        return updateAddTable($pdo, 'wait_debit_future');
    }

    // return ['status' => 'success']; # congrats, you did id, don't try anymore!
    return null;
}