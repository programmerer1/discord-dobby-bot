<?php

declare(strict_types=1);

namespace App\Controller;

use AttributeRouter\Route;
use App\Service\Env;
use App\Service\KeyAndPromtAction;
use App\Service\Response;
use App\Service\Validator;

class HomeController
{
    private $body;
    public readonly Response $response;
    public readonly Validator $validator;
    public readonly Env $env;
    public readonly KeyAndPromtAction $keyAndPromtAction;

    public function __construct(Env $env, Validator $validator, Response $response, KeyAndPromtAction $keyAndPromtAction)
    {
        $this->validator = $validator;
        $this->response = $response;
        $this->env = $env;
        $this->keyAndPromtAction = $keyAndPromtAction;
    }

    #[Route(path: '', methods: ['GET'], name: 'home')]
    public function index()
    {
        $this->response->send(type: 1);
    }

    #[Route(path: '/api', methods: ['POST'], name: 'api')]
    public function api()
    {
        $this->validator->checkJsonRequest();
        $this->body = file_get_contents('php://input');
        $this->validator->validateDiscordRequest($this->body, $this->env->env['DISCORD_PUBLIC_KEY']);
        $this->body = json_decode($this->body, true);
        $this->validator->validateBody($this->body);

        if (intval($this->body['type']) === 1) {
            $this->response->send(type: 1, flags: null);
        }
        $this->body['member']['user']['id'] = intval($this->body['member']['user']['id']);
        switch ($this->body['data']['name']) {
            case 'set-api-key':
                $this->validator->validateBodyOptions($this->body);
                $this->validator->validateApiKey($this->body['data']['options'][0]['value']);
                $this->keyAndPromtAction->processingSetApiKeyCommand($this->body);
                break;
            case 'promt':
                $this->validator->validateBodyOptions($this->body);
                $this->validator->validatePromt($this->body['data']['options'][0]['value']);
                $this->keyAndPromtAction->processingPromtCommand($this->body);
                break;
            case 'reset-api-key':
                $this->keyAndPromtAction->processingResetApiKeyCommand($this->body);
                break;
            default:
                $this->response->send(message: 'Error: Incorrect command');
        }
    }
}
