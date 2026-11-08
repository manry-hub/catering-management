<?php

namespace Tests\Support;

class DatabaseTester
{
    private static ?\mysqli $connection = null;
    private static array $envConfig = [];

    public static function loadEnv(): array
    {
        if (!empty(self::$envConfig)) {
            return self::$envConfig;
        }

        $envFile = dirname(__DIR__, 2) . '/.env.testing';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) continue;
                if (str_contains($line, '=')) {
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v);
                    self::$envConfig[$k] = $v;
                    putenv("$k=$v");
                    $_ENV[$k] = $v;
                }
            }
        }

        return self::$envConfig;
    }

    public static function getConnection(): \mysqli
    {
        if (self::$connection !== null && @mysqli_ping(self::$connection)) {
            return self::$connection;
        }

        self::loadEnv();

        $host   = getenv('DB_HOST') ?: 'localhost';
        $user   = getenv('DB_USER') ?: 'root';
        $pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
        $dbName = getenv('DB_NAME') ?: 'gorjes_test_db';
        $port   = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3307;
        $socket = getenv('DB_SOCKET') ?: '/tmp/gorjes_test.sock';

        $conn = @mysqli_connect($host, $user, $pass, $dbName, $port, $socket);
        if (!$conn) {
            throw new \RuntimeException(
                "Gagal terhubung ke database testing ($dbName via $socket / port $port): " . mysqli_connect_error()
            );
        }

        mysqli_set_charset($conn, 'utf8mb4');
        self::$connection = $conn;
        return self::$connection;
    }

    public static function closeConnection(): void
    {
        if (self::$connection !== null) {
            @mysqli_close(self::$connection);
            self::$connection = null;
        }
    }
}
