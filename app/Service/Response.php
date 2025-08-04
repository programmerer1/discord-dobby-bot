<?php

declare(strict_types=1);

namespace App\Service;

class Response
{
    public function send(?string $message = null, int $type = 4, ?int $flags = 64, bool $dieScript = true): void
    {
        $data['type'] = $type;

        if (!is_null($flags)) {
            $data['flags'] = $flags;
        }

        if (!is_null($message)) {
            $data['data']['content'] = $message;
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($dieScript === true) {
            exit;
        }
    }
}
