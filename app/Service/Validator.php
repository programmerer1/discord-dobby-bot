<?php

declare(strict_types=1);

namespace App\Service;

class Validator
{
    public function __construct(public readonly Response $response) {}

    public function validateApiKey(string $apiKey): void
    {
        if (strlen($apiKey) < 5 || strlen($apiKey) > 80) {
            $this->response->send(message: 'Error: It must be at least 5 characters and no more than 80 characters long.');
        }

        if (preg_match('/^[A-Za-z0-9_-]+$/', $apiKey) !== 1) {
            $this->response->send(message: 'Error: Only Latin letters, numbers, and the underscore (_) are allowed.');
        }
    }

    public function validatePromt(string $promt): void
    {
        if (strlen($promt) < 3 || strlen($promt) > 3000) {
            $this->response->send(message: 'Error: It must be at least 3 characters and no more than 1000 characters long.');
        }

        return;
    }

    public function issetDiscordHeaders(): bool
    {
        if (empty($_SERVER['HTTP_X_SIGNATURE_ED25519']) || empty($_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'])) {
            return false;
        }

        if (!is_string($_SERVER['HTTP_X_SIGNATURE_ED25519']) || !is_string($_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'])) {
            return false;
        }

        return true;
    }

    public function validateDiscordRequest(string $body, string $publicKey): void
    {
        if (($this->issetDiscordHeaders() === false)
            || (sodium_crypto_sign_verify_detached(
                hex2bin($_SERVER['HTTP_X_SIGNATURE_ED25519']),
                $_SERVER['HTTP_X_SIGNATURE_TIMESTAMP'] . $body,
                hex2bin($publicKey)
            ) === false)
        ) {
            $this->response->send(message: 'Error: Incorrect request - 1');
        }

        return;
    }

    public function validateBody(mixed $body): void
    {
        if (
            !is_array($body) ||
            empty($body['type']) ||
            !is_scalar($body['type'])
        ) {
            $this->response->send(message: 'Error: Incorrect request - 2');
        }

        if (intval($body['type']) === 1) {
            return;
        }

        if (
            empty($body['member']['user']['id']) ||
            !is_scalar($body['member']['user']['id']) ||
            empty($body['data']['name']) ||
            !is_string($body['data']['name']) ||
            empty($body['application_id']) ||
            !is_scalar($body['application_id']) ||
            empty($body['token']) ||
            !is_scalar($body['token'])
        ) {
            $this->response->send(message: 'Error: Incorrect request - 3');
        }

        return;
    }

    public function validateBodyOptions(mixed $body): void
    {
        if (
            empty($body['data']['options']) ||
            !is_array($body['data']['options']) ||
            empty($body['data']['options'][0]['value']) ||
            !is_string($body['data']['options'][0]['value'])
        ) {
            $this->response->send(message: 'Error: Incorrect request - 4');
        }

        return;
    }

    public function checkJsonRequest(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        if (str_contains($contentType, 'application/json') || str_contains($accept, 'application/json')) {
            return;
        }

        $this->response->send(message: 'Error: Must be a JSON request.');
    }
}
