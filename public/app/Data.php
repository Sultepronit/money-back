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

        // echo $pass;
        return password_verify($pass, $hash);
    }

    private static function getData(): array
    {
        $query = "SELECT * FROM main_table";
        $data = self::$pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    private static function addNewRows(): void
    {
        $query = 'SELECT date FROM main_table ORDER BY date DESC LIMIT 1';
        $lastDate = self::$pdo->query($query)->fetch(PDO::FETCH_COLUMN);

        $today = DateHandler::getToday();

        if($lastDate === $today) return; 

        $dates = DateHandler::generateDateSpan($lastDate, $today);
        $version = updateVersion(self::$pdo);

        $query = "INSERT INTO main_table (`date`, v, data_usd_rate, data_eur_rate)
            VALUES (?, ?, ?, ?)";
        $stmt = self::$pdo->prepare($query);

        foreach($dates as $date) {
            $usdRate = $date === $today ? getRate('usd') : null;
            $eurRate = $date === $today ? getRate('eur') : null;
            $stmt->execute([$date, $version, $usdRate, $eurRate]);
        }
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

        self::addNewRows();
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

        self::addNewRows();

        if($receivedVersion !== getDbVersion(self::$pdo)) {
            return self::prepareForSending(self::getData());
        }

        return ['status' => 'up-to-date'];
    }

    private static function getDataNew(int $sinceVersion = 0): array
    {
        // others_marta - to remove
        
        // common_usd_rate -> data_usd_rate
        // common_eur_rate -> data_eur_rate
        // income_cancel -> common_income_cancel
        // income_debit -> stefko_deposit_income

        $columnsList = ['date', 'common_cash', 'common_usd', 'common_usd_exchanges', 'data_usd_rate', 'data_eur_rate', 'stefko_deposit_income', 'common_income_cancel', 'stefko_credit_1', 'stefko_credit_2', 'stefko_credit_3', 'stefko_credit_4', 'stefko_debit_1', 'stefko_debit_2', 'stefko_debit_3', 'stefko_debit_4', 'stefko_debit_5', 'stefko_eur', 'stefko_eur_exchanges', 'stefko_income', 'vira_black', 'vira_black_income', 'vira_white', 'vira_white_income', 'vira_cash_income', 'vira_cash_expense']; 

        $columnsQuery = createStringColumnAsName($columnsList);

        $condition = $sinceVersion ? "WHERE v > $sinceVersion" : '';

        $query = "SELECT $columnsQuery FROM main_table $condition";
        $data = self::$pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    # new
    private static function sendData(array $input, string $session) {
        self::addNewRows();        

        $actualVersion = getDbVersion(self::$pdo);
        if($input['version'] !== $actualVersion) {

            $result = ['version' => $actualVersion];

            if ($session) $result['session'] = $session;

            if ($input['version'] === 0) {
                $result['data'] = self::getDataNew();
                $result['futureJson'] = self::getWaitDebit();
            } else {
                $result['patches'] = self::getDataNew($input['version']);
                $future = self::getWaitDebit();
                if ($future !== $input['futureJson']) $result['futureJson'] = $future;
            }            

            return $result;
        } else if($session) {
            return ['session' => $session];
        }

        return ['status' => 'up-to-date'];
    }

    public static function refreshNew($pdo): ?array
    {
        self::$pdo = $pdo;

        $json = file_get_contents('php://input');
        $input = parseJson($json, ['version', 'futureJson', 'session']);

        if (!$input) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        return self::sendData($input, checkSession($input['session'], self::$pdo));
    }

    public static function login(PDO $pdo): ?array
    {
        self::$pdo = $pdo;

        $json = file_get_contents('php://input');
        $input = parseJson(
            $json,
            ['password', 'username', 'passphrase', 'version', 'futureJson', 'session']
        );

        if (!$input || !checkPassword($input['password'], self::$pdo)) {
            return ['status' => 'success']; # congrats, you did id, don't try anymore!
        }

        $actualSession = getActualSession(self::$pdo);
        $session = $actualSession === $input['session'] ? '' : $actualSession;

        return self::sendData($input, $session);
    }
}