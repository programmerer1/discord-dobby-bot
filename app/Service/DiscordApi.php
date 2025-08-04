<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use \Throwable;

class DiscordApi
{
    private string $url;
    public readonly string $modelName;
    private $httpClient;
    public readonly ServicesExceptionHandler $servicesExceptionHandler;

    public function __construct(Env $env, ServicesExceptionHandler $servicesExceptionHandler)
    {
        $this->servicesExceptionHandler = $servicesExceptionHandler;
        $this->httpClient = HttpClient::create();
        $this->url = $env->env['DISCORD_WEBHOOK_URL'];
    }

    public function send(string $content, array $body)
    {
        try {
            $this->url = sprintf($this->url, $body['application_id'], $body['token']);
            $this->httpClient->request('POST', $this->url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode(['content' => $content], JSON_UNESCAPED_UNICODE),
            ]);
            exit;
        } catch (Throwable $e) {
            $this->servicesExceptionHandler->logAndSendResponse($e, 'api_request_error.log');
        }
    }
}
