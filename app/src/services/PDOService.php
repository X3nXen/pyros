<?php
namespace Service;
use PDO;
use PDOException;

class PDOService{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            
            $config = require ROOT_PATH . "/config.php";

            $dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD'], $options);
            } catch (PDOException $e) {
                die("Adatbázis hiba: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}