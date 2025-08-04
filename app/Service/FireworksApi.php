<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use \Throwable;

class FireworksApi
{
    public readonly string $url;
    public readonly string $modelName;
    private $httpClient;
    public readonly ServicesExceptionHandler $servicesExceptionHandler;

    public function __construct(Env $env, ServicesExceptionHandler $servicesExceptionHandler)
    {
        $this->servicesExceptionHandler = $servicesExceptionHandler;
        $this->httpClient = HttpClient::create();
        $this->url = $env->env['API_URL'];
        $this->modelName = $env->env['MODEL_NAME'];
    }

    public function send(string $apiKey, string $promt)
    {
        try {
            $payload = [
                'model' => $this->modelName,
                'max_tokens' => 16384,
                'top_p' => 1,
                'top_k' => 40,
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                'temperature' => 0.5,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $promt
                    ]
                ]
            ];

            $response = $this->httpClient->request('POST', $this->url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            return $this->formatAnswer($response->getContent());
        } catch (Throwable $e) {
            $this->servicesExceptionHandler->logAndSendResponse($e, 'api_request_error.log');
        }
    }

    public function formatAnswer(string $content): array
    {
        $data = json_decode($content, true);
        return [
            'id' => $data['id'],
            'message' => $data['choices'][0]['message']['content']
        ];
    }
}
