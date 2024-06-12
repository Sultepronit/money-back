<?php
declare(strict_types=1);
function getUsdRate() {
    $ch = curl_init('https://minfin.com.ua/ua/currency/usd/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $firstPageContent = curl_exec($ch);
    curl_close($ch);

    // echo $firstPageContent;

    libxml_use_internal_errors(true);
    $html = new DOMDocument();
    $html->loadHTML($firstPageContent);

    $firstTbody = $html->getElementsByTagName('tbody')->item(0);
    // print_r($firstTbody);
    $firstDiv = $firstTbody->getElementsByTagName('div')->item(0);
    // print_r($firstDiv);
    $firstP = $firstDiv->getElementsByTagName('p')->item(0);
    // print_r($firstP);
    $wholeText = $firstDiv->textContent;
    $redundantText = $firstP->textContent;
    // echo $wholeText . '<br>';
    // echo $redundantText . '<br>';

    $theRate = explode($redundantText, $wholeText)[0];
    $theRate = (float) str_replace(',', '.', $theRate);

    print_r(['rate' => round($theRate, 1)]);

    return ['rate' => round($theRate, 1)];
}