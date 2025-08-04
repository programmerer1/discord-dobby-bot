<?php

declare(strict_types=1);

namespace App\Service;

use \Throwable;

class ServicesExceptionHandler
{
    public function __construct(public readonly Response $response, public readonly Logger $logger) {}

    public function logAndSendResponse(Throwable $e, string $filename)
    {
        $message = $this->logger->formatThrowable($e);
        $this->logger->write(filename: ROOT . 'logs/' . $filename, message: $message);
        $this->response->send('ERROR: Failed to execute the command.');
    }
}
