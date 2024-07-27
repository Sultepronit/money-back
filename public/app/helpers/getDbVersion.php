<?php
declare(strict_types=1);

function getDbVersion($pdo) {
    $query = "SELECT * FROM last_version";
    return $pdo->query($query)-> fetch(PDO::FETCH_COLUMN);
}