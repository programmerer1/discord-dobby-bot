<?php

declare(strict_types=1);

namespace App\Service;

use \ErrorException;
use \Throwable;

class ErrorHandler
{
    private string $logFile = ROOT . 'logs/error.log';
    private Response $response;
    private Logger $logger;

    public function __construct(Response $response, Logger $logger)
    {
        $this->response = $response;
        $this->logger = $logger;
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleException(Throwable $exception): void
    {
        $message = $this->logger->formatThrowable($exception);
        $this->logger->write(filename: $this->logFile, message: $message);
        $this->response->send(message: 'Error: Something went wrong. Please try again or contact the administrator');
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function handleShutdown(): void
    {
        if (($error = error_get_last()) !== null) {
            $exception = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $this->handleException($exception);
        }
    }
}
