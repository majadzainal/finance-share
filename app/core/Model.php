<?php

namespace App\Core;

use PDO;

class Model
{
    private static ?PDO $connection = null;

    protected function db(): PDO
    {
        if (self::$connection === null) {
            $config = config('database');
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        }

        return self::$connection;
    }
}
