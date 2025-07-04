<?php
declare(strict_types=1);

function parseJson(mixed $json, array $isset = []): ?array {
    try {
        $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($result)) return null;

        foreach($isset as $field) {
            if(!isset($result[$field])) return null;
        }

        return $result;
    } catch (\Throwable $th) {
        return null;
    }
}

// echo 'hello!' . PHP_EOL;