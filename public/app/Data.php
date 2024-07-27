<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers/DateHandler.php';

class Data
{
    private static PDO $pdo;

    private static function checkRefreshData($passdata): string
    {
        $passdata = json_decode($passdata, true);

        $fields = ['date', 'column', 'value', 'version'];
        foreach($fields as $field) {
            if(!isset($passdata[$field])) return '';
        }
        
        $query = "SELECT {$passdata['column']} FROM main_table
            WHERE date = '{$passdata['date']}'";
 
        $value = self::$pdo->query($query)->fetch(PDO::FETCH_COLUMN);

        if($value != $passdata['value']) return '';

        $query = "SELECT * FROM last_version";
        $lastVersion = self::$pdo->query($query)-> fetch(PDO::FETCH_COLUMN);
        
        return $passdata['version'] == $lastVersion ? 'up-to-date' : 'update';
    }

    private static function checkPassword($pass): bool
    {
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

    public static function run($pdo): ?array
    {
        self::$pdo = $pdo;

        $pass = file_get_contents('php://input');

        $doRefresh = self::checkRefreshData($pass);

        if(!$doRefresh && !self::checkPassword($pass)) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        $data = self::get(); // maybe it's just wrong to get all the data at each refresh!!!
        $delay = self::checkDateDelay($data);

        if($delay) {
            self::populateTheDb($delay);
            $data = self::get();
        } else if($doRefresh === 'up-to-date') {
            return ['status' => 'up-to-date'];
        }

        $dbVersion = getDbVersion(self::$pdo);

        return [
            'data' => $data,
            'version' => $dbVersion
        ];
    }
}