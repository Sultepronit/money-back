<?php
declare(strict_types=1);

function update(PDO $pdo, $date): array
{
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
        ? ['success' => true] : compact('input', 'result');
}