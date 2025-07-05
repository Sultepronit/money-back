<?php
declare(strict_types=1);

function updateVersion(PDO $pdo): int
{
    $lastVersion = getDbVersion($pdo);

    $updated = $lastVersion + 1;

    $query = "UPDATE add_table SET db_version = {$updated}
        WHERE db_version = {$lastVersion}";
    $pdo->exec($query);

    return $updated;
}

function updateNew(array $data, string $date, PDO $pdo): array {
    ['name' => $name, 'value' => $value, 'session' => $session] = $data;

    if (checkSession($session, $pdo) === 'none') return ['status' => 'success'];

    # get new version
    $version = updateVersion($pdo);

    # set data to db
    $column = camelToSnake($name);
    $query = "UPDATE main_table SET {$column} = ?, v = ? WHERE `date` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$value, $version, $date]);

    $rowsAffected = $stmt->rowCount();
    return $rowsAffected ? ['version' => $version] : ['rowsAffected' => $rowsAffected];
}

function update(PDO $pdo, $date): array
{
    # receive the data
    $json = file_get_contents('php://input');

    # check if maybe it's newUpdate case
    // $newUpdateData = parseJson($json, ['name', 'value', 'session']);
    $newUpdateData = parseJson($json, ['name', 'session']); # for value === null case
    if ($newUpdateData) {
        return updateNew($newUpdateData, $date, $pdo);
    }

    # continue with old one, while checking that data is expected
    try {
        [$column, $value, $passdata] = json_decode($json);
    } catch (\Throwable $th) {
        return ['status' => 'success'];
    }

    if(!checkPassdata($passdata, $pdo)) return ['status' => 'success'];

    # get new version
    $version = updateVersion($pdo);

    # set data to db
    $query = "UPDATE main_table SET {$column} = ?, v = ? WHERE `date` = ?";
    $pdo->prepare($query)->execute([$value, $version, $date]);

    return ['version' => $version];

    // # fetch it back
    // $query = "SELECT {$column} FROM main_table WHERE `date` = ?";
    // $stmt = $pdo->prepare($query);
    // $stmt->execute([$date]);
    // $result = $stmt->fetch(PDO::FETCH_COLUMN);

    // # send the results
    // return (string) $result === (string) $value
    //     ? ['version' => $version] : compact('value', 'result');
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