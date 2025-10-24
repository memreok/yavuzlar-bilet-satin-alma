<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

date_default_timezone_set('Europe/Istanbul');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



$db_path = __DIR__ . '/database/bilet-satin-alma-database.db';

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
    die("Sistemde bir sorun oluştu. Lütfen daha sonra tekrar deneyin.");
}
?>