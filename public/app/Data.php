<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers/DateHandler.php';

class Data
{
    private static PDO $pdo;

    private static function checkPassword($passData): bool // to be moved out?
    {
        // echo $passData;
        // $pass = null;
        try {
            $strong = json_decode($passData, true);
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

        if(self::addLastDates() || $receivedVersion !== getDbVersion(self::$pdo)) {
            return self::prepareForSending(self::getData());
        }

        return ['status' => 'up-to-date'];
    }

    private static function getDataNew(int $sinceVersion = 0): array
    {
        // others_marta - to remove
        $columns = "date,
            common_cash as commonCash,
            common_usd as commonUsd,
            common_usd_exchanges as commonUsdExchanges,
            common_usd_rate as dataUsdRate,
            common_eur_rate as dataEurRate,
            income_debit as stefkoDepositIncome,
            income_cancel as commonIncomeCancel,
            stefko_credit_1 as stefkoCredit1,
            stefko_credit_2 as stefkoCredit2,
            stefko_credit_3 as stefkoCredit3,
            stefko_credit_4 as stefkoCredit4,
            stefko_debit_1 as stefkoDebit1,
            stefko_debit_2 as stefkoDebit2,
            stefko_debit_3 as stefkoDebit3,
            stefko_debit_4 as stefkoDebit4,
            stefko_debit_5 as stefkoDebit5,
            stefko_eur as stefkoEur,
            stefko_eur_exchanges as stefkoEurExchanges,
            stefko_income as stefkoIncome,
            vira_black as viraBlack,
            vira_black_income as viraBlackIncome,
            vira_white as viraWhite,
            vira_white_income as viraWhiteIncome,
            vira_cash_income as viraCashIncome,
            vira_cash_expense as viraCashExpense
        ";

        $condition = $sinceVersion ? "WHERE v > $sinceVersion" : '';

        $query = "SELECT $columns FROM main_table $condition";
        $data = self::$pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public static function receiveNew($pdo): array
    {
        self::$pdo = $pdo;

        $pass = file_get_contents('php://input');
        // echo($pass);

        if(!self::checkPassword($pass)) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        self::addLastDates();
        return self::prepareForSending(self::getDataNew());
    }

    public static function refreshNew($pdo): ?array
    {
        self::$pdo = $pdo;

        $json = file_get_contents('php://input');
        $input = json_decode($json, true);

        if(!checkPassdataNew($input, self::$pdo)) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        self::addLastDates();

        if($input['version'] !== getDbVersion(self::$pdo)) {
            // return self::prepareForSending(self::getData());
            // return ['status' => 'to be updated!'];
            return [
                'patches' => self::getDataNew($input['version']),
                // 'wait_debit' => self::getWaitDebit(),
                'version' => getDbVersion(self::$pdo)
            ];
        }

        return ['status' => 'up-to-date'];
    }
}