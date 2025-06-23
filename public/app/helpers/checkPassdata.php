<?php
declare(strict_types=1);

function checkPassdata(mixed $passdata, PDO $pdo): bool|int
{    
    try {
        $passdata = json_decode($passdata, true);

        $fields = ['date', 'column', 'value'];
        foreach($fields as $field) {
            if(!isset($passdata[$field])) return false;
        }
        
        // $query = "SELECT {$passdata['column']} FROM main_table WHERE date = '{$passdata['date']}'";
        $query = "SELECT {$passdata['column']} FROM main_table WHERE date = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$passdata['date']]);

        // $value = $pdo->query($query)->fetch(PDO::FETCH_COLUMN);
        $value = $stmt->fetch(PDO::FETCH_COLUMN);

        if($value != $passdata['value']) return false;
    } catch (\Throwable $th) {
        return false;
    }

    return isset($passdata['version']) ? $passdata['version'] : true;
}

function checkPassdataNew(mixed $passdata, PDO $pdo): bool
{    
    try {
        // $passdata = json_decode($passdata, true);

        $expectedFields = ['version', 'futureJson'];
        foreach($expectedFields as $field) {
            if(!isset($passdata[$field])) return false;
        }
        
        // $query = "SELECT {$passdata['column']} FROM main_table WHERE date = ?";
        // $stmt = $pdo->prepare($query);
        // $stmt->execute([$passdata['date']]);

        // $value = $stmt->fetch(PDO::FETCH_COLUMN);

        // if($value != $passdata['value']) return false;
    } catch (\Throwable $th) {
        return false;
    }

    return true;
}
