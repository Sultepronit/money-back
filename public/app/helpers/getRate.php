<?php
declare(strict_types=1);
function getRate($currency): float {
    try {
        $ch = curl_init("https://minfin.com.ua/ua/currency/$currency/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        libxml_use_internal_errors(true);
        $html = new DOMDocument();
        $html->loadHTML($pageContent);

        $firstTbody = $html->getElementsByTagName('tbody')->item(0);
        $firstDiv = $firstTbody->getElementsByTagName('div')->item(0);
        $firstP = $firstDiv->getElementsByTagName('p')->item(0);
        $wholeText = $firstDiv->textContent;
        $redundantText = $firstP->textContent;

        $theRate = str_replace($redundantText, '', $wholeText);
        $theRate = (float) str_replace(',', '.', $theRate);

        return round($theRate, 1);
    } catch (\Throwable $th) {
        return 0.0;
    }
    
}

// print_r(getRate('eur'));
// print_r(getRate('usd'));