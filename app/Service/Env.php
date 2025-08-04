<?php

declare(strict_types=1);

namespace App\Service;

use Dotenv\Dotenv;

class Env
{
    public readonly mixed $env;

    public function __construct()
    {
        $dotEnv = Dotenv::createImmutable(ROOT);
        $this->env = $dotEnv->load();
    }
}
