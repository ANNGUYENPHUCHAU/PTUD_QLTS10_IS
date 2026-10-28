<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    /** @param array{dsn:string, username:?string, password:?string} $config */
    public static function connect(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        try {
            self::$connection = new PDO(
                $config['dsn'],
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            if (str_starts_with($config['dsn'], 'sqlite:')) {
                self::$connection->exec('PRAGMA foreign_keys = ON');
                self::$connection->exec('PRAGMA busy_timeout = 5000');
            }

            return self::$connection;
        } catch (PDOException $e) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu.', 0, $e);
        }
    }

    public static function setConnection(?PDO $connection): void
    {
        self::$connection = $connection;
    }
}

