<?php
declare(strict_types=1);

function checkPassdata(string $passdata, PDO $pdo): bool|int
{
    $passdata = json_decode($passdata, true);

    $fields = ['date', 'column', 'value'];
    foreach($fields as $field) {
        if(!isset($passdata[$field])) return false;
    }
    
    try {
        $query = "SELECT {$passdata['column']} FROM main_table
            WHERE date = '{$passdata['date']}'";

        $value = $pdo->query($query)->fetch(PDO::FETCH_COLUMN);

        if($value != $passdata['value']) return false;
    } catch (\Throwable $th) {
        return false;
    }

    return isset($passdata['version']) ? $passdata['version'] : true;
}
