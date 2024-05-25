<?php

class Database
{
    private static $dbh = null;

    private function __construct() {}

    public static function getConnection()
    {
        if (self::$dbh === null) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/../libraryConfig.php';

            try {
                // Instantiate our PDO Database Object
                self::$dbh = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
                echo "Connected to database";
            } catch (PDOException $e) {
                // This is good for debugging but not for a real-life project, because it will show the username
                die($e->getMessage());
            }
        }
        return self::$dbh;
    }
}

