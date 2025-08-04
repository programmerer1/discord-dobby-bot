<?php

declare(strict_types=1);

namespace App\Model;

use App\Service\Db;
use App\Service\ServicesExceptionHandler;
use \Throwable;

class Model
{
    public function __construct(public readonly Db $db, public readonly ServicesExceptionHandler $servicesExceptionHandler) {}

    public function getUser(int $id)
    {
        return $this->db->request('SELECT id, api_key, created_at, updated_at FROM users WHERE id = ?', [$id])->fetch();
    }

    public function addUser(int $id, ?string $apiKey, string $dateTime)
    {
        try {
            return $this->db->request(
                'INSERT INTO users (id, api_key, created_at, updated_at) VALUES (?, ?, ?, ?)',
                [$id, $apiKey, $dateTime, $dateTime]
            );
        } catch (Throwable $e) {
            $this->servicesExceptionHandler->logAndSendResponse($e, 'db_request_error.log');
        }
    }

    public function changeApiKey(int $id, ?string $apiKey, string $dateTime)
    {
        try {
            return $this->db->request('UPDATE users SET api_key = ?, updated_at = ? WHERE id = ?', [$apiKey, $dateTime, $id]);
        } catch (Throwable $e) {
            $this->servicesExceptionHandler->logAndSendResponse($e, 'db_request_error.log');
        }
    }

    public function getLastPromt(int $user_id)
    {
        return $this->db->request(
            'SELECT created_at FROM user_promt_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1',
            [$user_id]
        )->fetch();
    }

    public function addPromtRequest(int $user_id, string $dateTime)
    {
        try {
            return $this->db->request(
                'INSERT INTO user_promt_requests (user_id, created_at) VALUES (?, ?)',
                [$user_id, $dateTime]
            );
        } catch (Throwable $e) {
            $this->servicesExceptionHandler->logAndSendResponse($e, 'db_request_error.log');
        }
    }
}
