<?php

declare(strict_types=1);

namespace App\Service;

use \PDO;
use \PDOStatement;
use App\Service\Env;

class Db
{
    public readonly PDO $pdo;

    public function __construct(Env $env)
    {
        $this->pdo = new PDO($env->env['DB_DSN'], $env->env['DB_USERNAME'], $env->env['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function request(string $sql, array $params): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
