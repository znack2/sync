<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Helpers;

class SyncEngineHelper {

    private $service;

    public function __construct()
    {
        $this->service = config('connection.host');
    }

    public function getAddr()
    {
        return $this->service;
    }

    public function createAccount(string $email, string $password)
    {
        $params = [
            'email'=>$email,
            'password'=>$password
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json' . PHP_EOL,
                'content' => json_encode($params),
                'ignore_errors' => true
            ],
        ]);

        $path = 'http://' . $this->service . '/connect/authorize';

        $response = file_get_contents($path, false, $context);

        return json_decode($response, true);
    }

    public function deleteAccount(string $account_id)
    {

    }

}