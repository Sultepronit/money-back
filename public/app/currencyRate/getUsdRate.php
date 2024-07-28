<?php
declare(strict_types=1);
function getUsdRate() {
    $ch = curl_init('https://minfin.com.ua/ua/currency/usd/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $pageContent = curl_exec($ch);
    curl_close($ch);

    libxml_use_internal_errors(true);
    $html = new DOMDocument();
    $html->loadHTML($pageContent);

    $firstTbody = $html->getElementsByTagName('tbody')->item(0);
    // print_r($firstTbody);
    $firstDiv = $firstTbody->getElementsByTagName('div')->item(0);
    // print_r($firstDiv);
    $firstP = $firstDiv->getElementsByTagName('p')->item(0);
    // print_r($firstP);
    $wholeText = $firstDiv->textContent;
    $redundantText = $firstP->textContent;

    $theRate = str_replace($redundantText, '', $wholeText);
    $theRate = (float) str_replace(',', '.', $theRate);

    // setcookie('c-name', 'wwwalue', [
    //     'expires' => time() + 36000,
    //     'path' => '/',
    //     'secure' => true, // Set to true if using HTTPS
    //     // 'httponly' => true,
    //     'samesite' => 'None' // Required for cross-origin requests
    // ]);

    return ['rate' => round($theRate, 1)];
    // $result = ['a' => 'b', ...$_COOKIE];
    // return $result;
}