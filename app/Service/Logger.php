<?php

declare(strict_types=1);

namespace App\Service;

use \Throwable;

class Logger
{
    public function write(string $filename, string $message): void
    {
        $date = date('Y-m-d H:i:s');
        file_put_contents($filename, "[$date] $message\n", FILE_APPEND);
    }

    public function formatThrowable(Throwable $e): string
    {
        return sprintf(
            "[Exception] %s: %s in %s on line %d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
    }
}
