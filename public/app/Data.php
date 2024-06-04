<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers/DateHandler.php';

class Data
{
    private static PDO $pdo;

    private static function checkPassword(): bool
    {
        $pass = file_get_contents('php://input');

        $query = "SELECT * FROM secure";
        $hash = self::$pdo->query($query)->fetch(PDO::FETCH_COLUMN);
        
        return password_verify($pass, $hash);
    }

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
        // echo $lastDate;

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

    public static function run($pdo): array
    {
        self::$pdo = $pdo;

        if(!self::checkPassword()) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        $data = self::get();
        $delay = self::checkDateDelay($data);

        if($delay) {
            self::populateTheDb($delay);
            $data = self::get();
        }

        return $data;
    }
}