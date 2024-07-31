<?php
declare(strict_types=1);

function getDbVersion($pdo) {
    $query = "SELECT db_version FROM add_table";
    return $pdo->query($query)-> fetch(PDO::FETCH_COLUMN);
}