<?php
declare(strict_types=1);

function myIsset(array $array, array $fields): bool {
    foreach($fields as $field) {
        if(!isset($array[$field])) return false;
    }

    return true;
}