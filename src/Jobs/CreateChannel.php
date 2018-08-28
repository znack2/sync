<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use App\Helpers\System\CurlHelper

use Usedesk\SyncIntegration\Exceptions\SyncException;

use App\Jobs\AbstractJob;

class CreateChannel extends AbstractJob
{
    private $params;
    private $data;
    private $helper;

    public function __construct(array $params = [],array $data = [])
    {
        $this->params = $params;
        $this->data = $data;
        $this->helper = new CurlHelper;
    }

    public function handle(): string
    {
        $params = $this->formatParams($this->data,$this->params);

        $path = 'http://' . $this->url . '/connect/authorize';

        $result = $this->helper->call($path,$params);

        if (in_array($result['type'],['api_error','oauth']) {
            new SyncException;
        }

        return $message;
    }

    private function formatParams(array $data = [], array $params = []): array
    {
        if(!empty($params)){
            $settings = [
                'imap_host'         => $data['params.imap.host'],
                'imap_port'         => $data['params.imap.port'] ?? 993,
                'imap_username'     => $data['params.imap.username'] 
                                            ??  $data['incoming_email'],
                'imap_password'     => $data['params.imap.password'],
                'smtp_host'         => $data['params.smtp.host'],
                'smtp_port'         => $data['params.smtp.port'] ?? 465,
                'smtp_username'     => $data['params.smtp.username'] 
                                            ??  $data['outgoing_email'],
                'smtp_password'     => $data['params.smtp.password'],
                //change to imap_encrypt
                //change to smtp_encrypt
                'ssl_required'      => $data['ssl_required'] ?? true,
                'auth_code'         => $data['auth_code'] ?? 'get',
                // token
            ];
        }

        $data = [
            'name'          => $this->data['name'],
            'email_address' => $this->data['incoming_email'],
            'reauth'        => (bool) data['external'], 
            'password'      => $this->settings['imap.password'],
            'settings'      => $settings,
            'auth_code'     => $this->data['auth_code'],
        ];

        return $params;
    }
}


