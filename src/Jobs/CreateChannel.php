<?php
namespace Usedesk\SyncEngineIntegration\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Usedesk\SyncEngineIntegration\helpers\SyncEngineHelper;

class CreateChannel implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $name;
    private $email;
    private $password;
    private $params;
    private $reauth;

    private $syncHelper;

    public function __construct(string $name, string $email, string $password = '', array $params = [], $reauth = false)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->params = $params;
        $this->reauth = $reauth;
    }

    public function handle()
    {
        $params = [
            'name' => $this->name,
            'email_address' => $this->email,
            'reauth' => $this->reauth,
        ];

        if (!empty($this->params['auth_code'])) {
            $params['auth_code'] = $this->params['auth_code'];
            unset($this->params['auth_code']);
        }

        if ($this->password) {
            $params['password'] = $this->password;
        }

        if ($this->params) {
            $params['settings'] = $params;
        }

        $result = SyncEngineHelper::createAccount($params);

        if (!empty($result['type'])) {
            return [
                'success' => false,
                'type' => $result['type'],
                'message' => $result['message'],
            ];
        }

        if (!empty($result['oauth2_url'])) {
            return [
                'success' => false,
                'type' => 'oauth',
                'oauth2_url' => $result['oauth2_url'],
            ];
        }

        return [
            'success' => true,
            'type' => 'success',
            'data' => $result,
        ];
    }

}