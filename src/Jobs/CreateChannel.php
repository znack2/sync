<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use App\Helpers\System\CurlHelper;

use Usedesk\SyncIntegration\Exceptions\SyncException;

use App\Jobs\AbstractJob;

class CreateChannel extends AbstractJob
{
    private $params;
    private $requestData;
    private $helper;

     /**
     */
    public function __construct(array $requestData = [],array $params = [])
    {
        $this->params = $params;
        $this->requestData = $requestData;
        $this->helper = new CurlHelper;
    }

     /**
     */
    public function handle(): array
    {
        $params = $this->formatParams($this->requestData,$this->params);

        $path = 'http://' . env('SYC_ENGINE_HOST', 'localhost') . ':5555' . '/connect/authorize';

        $result = $this->helper->call($path,$params);

        if (!empty($result['type']) && $result['type'] == 'api_error') { //in_array($result['type'],['api_error','oauth']
            throw new SyncException($result['message']);
        }

        if(!empty($result['oauth2_url'])){
            $response['url'] = $result['oauth2_url'];
        }else{
            $response['account_id'] = $result['account_id'];
        }

        return $response;
    }

     /**
     */
    private function formatParams(array $requestData = [], array $params = []): array
    {
        if(!empty($params)){
            $data['settings'] = [
                'imap_host'     => $params['imap']['host'],
                'imap_port'     => $params['imap']['port'] ?? 993,
                'imap_username' => $params['imap']['username']
                                    ?? $requestData['incoming_email'],
                'imap_password' => $params['imap']['password'],

                'smtp_host'     => $params['smtp']['host'],
                'smtp_port'     => $params['smtp']['port'] ?? 465,
                'smtp_username' => $params['smtp']['username']
                                    ?? $requestData['outgoing_email'],
                'smtp_password' => $params['smtp']['password'],
                
                'ssl_required'  => ($params['smtp']['encrypt'] || $params['imap']['encrypt']),
            ];
        }

        // 'reauth'        => (bool) requestData['external'], 

        $data['name']           = $requestData['name'];
        $data['email_address']  = $requestData['incoming_email'];
        $data['provider']       = $this->getProvider($requestData['incoming_email']) ?? 'custom';

        if(!in_array($data['provider'],['google','yandex'])){
            $data['password'] = $params['imap.password'];
        }

        return $data;
    }

    private function getProvider(string $email): string
    {
        $provider = substr(substr($email, strpos($email, '@') +1),0,-3);

        return $provider;
    }

    
}


