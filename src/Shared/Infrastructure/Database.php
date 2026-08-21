<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure;

use PDO;

final readonly class Database
{
    public function __construct(
        private string $host,
        private int $port,
        private string $database,
        private string $username,
        private string $password,
    ) {
    }

    public function connect(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->database,
        );

        return new PDO(
            $dsn,
            $this->username,
            $this->password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }
}