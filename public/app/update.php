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
    $version = updateVersion($pdo);

    # get the data
    $input = json_decode(file_get_contents('php://input'));
    // print_r($input);
    $column = $input[0];
    $value = $input[1];

    # set data to db
    $stmt = $pdo->prepare("UPDATE main_table SET {$column} = ? WHERE `date` = ?");
    $stmt->execute([$value, $date]);

    # fetch it back
    $query = "SELECT {$column} FROM main_table WHERE `date` = '$date'";
    $result = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);

    # check results
    return (string) $result[$column] === (string) $value
        // ? ['success' => true] : compact('input', 'result');
        ? ['version' => $version] : compact('input', 'result');
}