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

        $path = 'http://' . env('SYC_ENGINE_HOST', 'localhost') . ':5555' . '/connect/authorize';

        $result = $this->helper->call($path,$params);

        if (in_array($result['type'],['api_error','oauth']) {
            new SyncException;
        }

        return $message;
    }

    private function formatParams(array $requestData = [], array $params = []): array
    {
        if(!empty($params)){
            $$data['settings'] = [
                'imap_host'         => $requestData['params.imap.host'],
                'imap_port'         => $requestData['params.imap.port'] ?? 993,
                'imap_username'     => $requestData['params.imap.username'] 
                                            ??  $requestData['incoming_email'],
                'imap_password'     => $requestData['params.imap.password'],
                'smtp_host'         => $requestData['params.smtp.host'],
                'smtp_port'         => $requestData['params.smtp.port'] ?? 465,
                'smtp_username'     => $requestData['params.smtp.username'] 
                                            ??  $requestData['outgoing_email'],
                'smtp_password'     => $requestData['params.smtp.password'],
                'ssl_required'      => $requestData['ssl_required'] ?? true,
            ];
        }

        if(!in_array($requestData['provider'],['google','yandex'])){
            $data = [
                'name'          => $this->requestData['name'],
                'reauth'        => (bool) requestData['external'], 
                'password'      => $this->settings['imap.password'],
            ];
        }

        $data['email_address'] = $this->requestData['incoming_email'];
        
        return $params;
    }
}


