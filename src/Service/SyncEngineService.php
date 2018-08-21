<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Service;

class SyncEngineService {

    private $url = env('SYC_ENGINE_HOST', 'localhost') . ':5555';

    public function createAccount(string $email, string $password)
    {
        $params = [
            'email'     =>$email,
            'password'. =>$password
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json' . PHP_EOL,
                'content' => json_encode($params),
                'ignore_errors' => true
            ],
        ]);

        $path = 'http://' . $this->url . '/connect/authorize';

        $response = file_get_contents($path, false, $context);

        return json_decode($response, true);
    }
}