<?php

require_once('php_config.php');

class DB
{
    private static $pdo = null;

    public static function connect()
    {
        if (self::$pdo === null) {
            try {
                $host = DB_HOST;
                $db = DB_NAME;
                $user = DB_USER;
                $pass = DB_PASS; // No password
                $charset = 'utf8mb4';

                $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            }
            catch (PDOException $e) {
                die("DB Connection Error: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>