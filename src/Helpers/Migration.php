<?php

namespace Kykurniawan\Hmm\Helpers;

use Exception;
use Kykurniawan\Hmm\Constants;
use PDO;

class Migration
{
    private static function getMigrationPath()
    {
        $migrationPath = Config::get(Constants::CONF_MIGRATION_PATH);

        if (is_null($migrationPath)) {
            throw new Exception('Please specify migration path in configuration');
        }

        if (!is_dir($migrationPath)) {
            throw new Exception($migrationPath . ' is not a directory');
        }

        return realpath($migrationPath);
    }

    private static function getMigrationFiles()
    {

        $files = glob(realpath(self::getMigrationPath()) . '/*.php');

        return array_map(function ($file) {
            if (is_file($file)) return realpath($file);
        }, $files);
    }

    private static function prepareMigration()
    {
        self::createMigrationsTable();
    }

    private static function createMigrationsTable()
    {
        $query = <<<QUERY
        CREATE TABLE IF NOT EXISTS migrations (
            id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            migration_name VARCHAR(128) NOT NULL
        )
        QUERY;

        $stmt = DB::connection()->prepare($query);
        $stmt->execute();
    }

    private static function getMigrationHistories($order = 'ASC')
    {
        $stmt = DB::connection()->prepare('SELECT * FROM migrations ORDER BY migration_name ' . $order);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private static function insertMigrationHistory(string $migrationName)
    {
        $stmt = DB::connection()->prepare('INSERT INTO migrations (migration_name) VALUES (?)');
        $stmt->execute([$migrationName]);
    }

    private static function deleteMigrationHistory(string $migrationName)
    {
        $stmt = DB::connection()->prepare('DELETE FROM migrations WHERE migration_name = ?');
        $stmt->execute([$migrationName]);
    }

    public static function migrate()
    {
        self::prepareMigration();

        $migrated = array_map(function ($history) {
            return $history->migration_name;
        }, self::getMigrationHistories());

        $migrationFiles = self::getMigrationFiles();

        foreach ($migrationFiles as $migrationFile) {
            $explode = explode(DIRECTORY_SEPARATOR, $migrationFile);
            $name = $explode[sizeof($explode) - 1];
            /** @var \Kykurniawan\Hmm\Database\Migration */
            $migration = require_once $migrationFile;

            if (!in_array($name, $migrated)) {
                $migration->up();
                self::insertMigrationHistory($name);
            }
        };
    }

    public static function reset()
    {
        $histories = self::getMigrationHistories('DESC');

        foreach ($histories as $history) {
            /** @var \Kykurniawan\Hmm\Database\Migration */
            $migration = require_once self::getMigrationPath() . '/' . $history->migration_name;

            $migration->down();
            self::deleteMigrationHistory($history->migration_name);
        }
    }
}
