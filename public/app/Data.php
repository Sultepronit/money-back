<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers/DateHandler.php';

class Data
{
    private static PDO $pdo;

    private static function checkPassword($pass): bool
    {
        // echo $pass;
        try {
            $strong = json_decode($pass, true);
            if(isset($strong['password'])) {
                if(!isset($strong['username'])) return false;
                // if(!isset($strong['passphrase'])) return false;
                $pass = $strong['password'];
            }
        } catch (\Throwable $th) {
            # do nothing
        }
        $query = "SELECT * FROM secure";
        $hash = self::$pdo->query($query)->fetch(PDO::FETCH_COLUMN);
        
        return password_verify($pass, $hash);
    }

    private static function getData(): array
    {
        $query = "SELECT * FROM main_table";
        $data = self::$pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    private static function addLastDates(): bool
    {
        # check if last date is actual
        $query = 'SELECT date FROM main_table ORDER BY date DESC LIMIT 1';
        $lastDate = self::$pdo->query($query)->fetch(PDO::FETCH_COLUMN);

        $dates = DateHandler::generateDateSpan($lastDate);

        if(empty($dates)) return false; // no new dates to add

        # insert passed dates to the db
        // $stmt = self::$pdo->prepare("INSERT INTO main_table ('date') VALUES (:value)");
        $query = "INSERT INTO main_table (`date`, v) VALUES (?, ?)";
        $stmt = self::$pdo->prepare($query);

        foreach($dates as $date) {
            // $stmt->bindParam(':value', $date);
            // $stmt->execute();
            $stmt->execute([$date, updateVersion(self::$pdo)]);
        }

        # return updated data
        // return self::getData();
        return true; // just return true to indicate that the dates were added
    }

    private static function getWaitDebit() {
        $query = "SELECT wait_debit_future FROM add_table";
        return self::$pdo->query($query)-> fetch(PDO::FETCH_COLUMN);
    }

    private static function prepareForSending($data) {
        return [
            'data' => $data,
            'wait_debit' => self::getWaitDebit(),
            'version' => getDbVersion(self::$pdo)
        ];
    }

    public static function receive($pdo): array
    {
        self::$pdo = $pdo;

        $pass = file_get_contents('php://input');

        if(!self::checkPassword($pass)) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        // $updated = self::addLastDates();
        // if($updated) {
        //     return self::prepareForSending($updated);
        // } else {
        //     return self::prepareForSending(self::getData());
        // }

        self::addLastDates();
        return self::prepareForSending(self::getData());
    }

    public static function refresh($pdo): ?array
    {
        self::$pdo = $pdo;

        $passdata = file_get_contents('php://input');

        $receivedVersion = checkPassdata($passdata, self::$pdo);

        if(!$receivedVersion) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        // $updated = self::addLastDates();
        // if($updated) { # changed the date case
        //     return self::prepareForSending($updated);
        // } else { # the date is actual case
        //     if($receivedVersion === getDbVersion(self::$pdo)) {
        //         return ['status' => 'up-to-date'];
        //     } else {
        //         return self::prepareForSending(self::getData());
        //     }
        // }

        if(self::addLastDates() || $receivedVersion !== getDbVersion(self::$pdo)) {
            return self::prepareForSending(self::getData());
        }

        return ['status' => 'up-to-date'];
    }
}