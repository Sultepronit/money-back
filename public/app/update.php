<?php
declare(strict_types=1);

function updateVersion(PDO $pdo): int
{
    $lastVersion = getDbVersion($pdo);

    $updated = $lastVersion + 1;

    $query = "UPDATE last_version SET value = {$updated}
        WHERE value = {$lastVersion}";
    $pdo->exec($query);

    return $updated;
}

function update(PDO $pdo, $date): array
{
    # receive the data
    [$column, $value, $passdata] = json_decode(file_get_contents('php://input'));
    // echo $passdata;

    # set data to db
    $query = "UPDATE main_table SET {$column} = ? WHERE `date` = ?";
    $pdo->prepare($query)->execute([$value, $date]);

    # fetch it back
    $query = "SELECT {$column} FROM main_table WHERE `date` = '$date'";
    $result = $pdo->query($query)->fetch(PDO::FETCH_COLUMN);

    # send the results
    return (string) $result === (string) $value
        ? ['version' => updateVersion($pdo)] : compact('value', 'result');
}

function updateAddTable(PDO $pdo, $column): array
{
    # receive the data
    [$value, $passdata] = json_decode(file_get_contents('php://input'));

    if(!checkPassdata($passdata, $pdo)) return ['status' => 'success'];

    # set data to db
    $query = "UPDATE add_table SET {$column} = ?
        WHERE rowid = (SELECT rowid FROM add_table LIMIT 1)";
    $pdo->prepare($query)->execute([$value]);

    # fetch it back
    $query = "SELECT {$column} FROM add_table";
    $result = $pdo->query($query)->fetch(PDO::FETCH_COLUMN);

    # send the results
    return (string) $result === (string) $value
        ? ['version' => updateVersion($pdo)] : compact('value', 'result');
}