<?php

declare(strict_types=1);

namespace App\Service;

use \DateTimeImmutable;
use App\Model\Model;

class KeyAndPromtAction
{
    public readonly DateTimeImmutable $date;
    private $user;
    public readonly Model $model;
    public readonly Response $response;
    public readonly FireworksApi $fireworksApi;
    public readonly Env $env;
    public readonly DiscordApi $discordApi;

    public function __construct(Model $model, Response $response, FireworksApi $fireworksApi, Env $env, DiscordApi $discordApi)
    {
        $this->model = $model;
        $this->date = new DateTimeImmutable('now');
        $this->response = $response;
        $this->fireworksApi = $fireworksApi;
        $this->env = $env;
        $this->discordApi = $discordApi;
    }

    public function processingSetApiKeyCommand(array $body)
    {
        $this->user = $this->model->getUser((int) $body['member']['user']['id']);

        if (!empty($this->user)) {
            $last = new DateTimeImmutable($this->user['updated_at']);
            $seconds = $this->date->getTimestamp() - $last->getTimestamp();

            if ($seconds < 3600) {
                $minutes = ceil((3600 - $seconds) / 60);
                $this->response->send(message: "Warning: You can change the key once per hour. Time left: $minutes min.");
            }

            $this->model->changeApiKey(
                id: $this->user['id'],
                apiKey: $body['data']['options'][0]['value'],
                dateTime: $this->date->format('Y-m-d H:i:s')
            );
        } else {
            $this->model->addUser(
                id: $body['member']['user']['id'],
                apiKey: $body['data']['options'][0]['value'],
                dateTime: $this->date->format('Y-m-d H:i:s')
            );
        }

        $this->response->send(message: 'API key saved. You can now start sending prompts.');
    }

    public function processingPromtCommand(array $body)
    {
        $this->user = $this->model->getUser($body['member']['user']['id']);

        if (empty($this->user)) {
            $this->model->addUser(
                id: $body['member']['user']['id'],
                apiKey: null,
                dateTime: $this->date->format('Y-m-d H:i:s')
            );

            $this->user = [
                'id' => $body['member']['user']['id'],
                'api_key' => null
            ];
        }

        if ($this->user['api_key'] === null) {
            $this->user['api_key'] = $this->env->env['DEFAULT_API_KEY'];
        }

        $lastPromt = $this->model->getLastPromt(user_id: $this->user['id']);

        if (!empty($lastPromt)) {
            $last = new DateTimeImmutable($lastPromt['created_at']);
            $seconds = $this->date->getTimestamp() - $last->getTimestamp();

            if ($seconds < 60) {
                /*$timeLeft = (ceil((60 - $seconds) / 60)) . ' min.';*/
                $timeLeft = (60 - $seconds) . ' sec.';
                $this->response->send(message: 'Warning: The prompt command can be sent once every 1 minute. Time left: ' . $timeLeft);
            }
        }

        /* Сообщаем Discord, что ответим позже */
        $this->response->send(type: 5, flags: null, dieScript: false);

        if (function_exists('fastcgi_finish_request')) {
            /* Закрываем соединение с Discord, но продолжаем выполнение кода */
            fastcgi_finish_request();
        }

        $answer = $this->fireworksApi->send($this->user['api_key'], $body['data']['options'][0]['value']);
        $this->model->addPromtRequest(user_id: $this->user['id'], dateTime: $this->date->format('Y-m-d H:i:s'));
        /*$this->response->send(message: $answer['message']);*/
        $this->discordApi->send($answer['message'], $body);
    }

    public function processingResetApiKeyCommand(array $body)
    {
        $this->user = $this->model->getUser($body['member']['user']['id']);

        if (empty($this->user)) {
            $this->model->addUser(
                id: $body['member']['user']['id'],
                apiKey: null,
                dateTime: $this->date->format('Y-m-d H:i:s')
            );
        } else {
            $this->model->changeApiKey(
                id: $this->user['id'],
                apiKey: null,
                dateTime: $this->date->format('Y-m-d H:i:s')
            );
        }

        $this->response->send(message: 'API key deleted');
    }
}
