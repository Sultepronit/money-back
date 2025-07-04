<?php
declare(strict_types=1);

function pushNewSession($today, PDO $pdo): string {
    $newCode = bin2hex(random_bytes(32));
    
    $populate = "INSERT INTO client_sessions (`date`, code) VALUES ('$today', '$newCode')";
    $success = $pdo->exec($populate);

    if (!$success) return getActualSession($pdo);

    $countQuery = "SELECT COUNT(*) AS row_cout FROM client_sessions";
    $count = $pdo->query($countQuery)->fetch(PDO::FETCH_COLUMN);
    // var_dump($count);

    if ($count > 10) {
        $delete = "DELETE FROM client_sessions WHERE rowid = (
            SELECT rowid FROM client_sessions ORDER BY rowid ASC LIMIT 1
        )";

        $pdo->exec($delete);
    }

    return $newCode;
}

function getActualSession(PDO $pdo): string {
    $today = DateHandler::getToday();

    $select = "SELECT code FROM client_sessions WHERE `date` = '$today' LIMIT 1";
    $todayCode = $pdo->query($select)->fetch(PDO::FETCH_COLUMN);

    if ($todayCode) return $todayCode;

    return pushNewSession($today, $pdo);
}


/**
 * @return '' (for up-to-date) | '[actual session]' | 'none'
 */
function checkSession(mixed $session, PDO $pdo): string {
    if (!is_string($session) || !preg_match('/^[a-f0-9]{64}$/', $session)) return 'none';

    $actualSession = getActualSession($pdo);

    if ($session === $actualSession) return ''; # up to date, nothing to shout

    $stmt = $pdo->prepare("SELECT `date` FROM client_sessions WHERE code = ? LIMIT 1");
    $stmt->execute([$session]);
    $isStale = $stmt->fetchColumn();
    
    if ($isStale) return $actualSession;

    return 'none';
}