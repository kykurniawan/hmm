<?php

namespace Kykurniawan\Hmm\Helpers;

use Kykurniawan\Hmm\Constants;
use PDO;
use PDOException;

class DB
{
    private static ?PDO $connection = null;

    private function __construct()
    {
        // Not implemented
    }

    public static function connection(): PDO
    {
        try {
            if (is_null(self::$connection)) {
                $dbhost = Config::get(Constants::CONF_DATABASE_HOST, '127.0.0.1');
                $dbport = Config::get(Constants::CONF_DATABASE_PORT, '3306');
                $dbuser = Config::get(Constants::CONF_DATABASE_USERNAME, 'root');
                $dbpass = Config::get(Constants::CONF_DATABASE_PASSWORD, 'root');
                $dbname = Config::get(Constants::CONF_DATABASE_NAME, 'hmm');
                
                self::$connection = new PDO(sprintf("%s:%s=%s:%s;dbname=%s", 'mysql', 'host', $dbhost, $dbport, $dbname), $dbuser, $dbpass);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return self::$connection;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function begin()
    {
        self::connection()->beginTransaction();
    }

    public static function commit()
    {
        self::connection()->commit();
    }

    public static function rollBack()
    {
        self::connection()->rollBack();
    }
}
