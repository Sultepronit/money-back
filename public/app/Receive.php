<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers/DateHandler.php';

class Receive
{
    private static PDO $pdo;

    private static function get(): array
    {
        $query = "SELECT * FROM main_table";
        $data = self::$pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    private static function checkDateDelay(array $data): ?array
    {
        $lastRecordIndex = count($data) - 1;
        $lastRecord = $data[$lastRecordIndex];
        $lastDate = $lastRecord['date'];

        $span = DateHandler::generateDateSpan($lastDate);

        return empty($span) ? null : $span;
    }

    private static function populateTheDb($dates): void
    {
        $stmt = self::$pdo->prepare("INSERT INTO main_table ('date') VALUES (:value)");

        foreach($dates as $date) {
            $stmt->bindParam(':value', $date);
            $stmt->execute();
        }
    }

    public static function run($pdo)
    {
        self::$pdo = $pdo;

        $data = self::get();
        $delay = self::checkDateDelay($data);

        if($delay) {
            self::populateTheDb($delay);
            $data = self::get();
        }

        echo json_encode($data);
    }
}