<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use App\Helpers\System\CurlHelper;

use App\Jobs\AbstractJob;

class CheckChannel extends AbstractJob
{
    protected $auth_code;
    protected $email_address;
    protected $name;
    protected $provider;
    protected $helper;

    public function __construct(string $auth_code,string $email_address,string $name,string $provider)
    {
        $this->auth_code = $auth_code;
        $this->email_address = $email_address;
        $this->name = $name;
        $this->provider = $provider;
        $this->helper = new CurlHelper;
    }

    public function handle(): string
    {
        $params = [
            'auth_code'     => $this->auth_code,//$this->requestData['auth_code'],
            'email_address' => $this->email_address,
            'provider'      => $this->provider,
            'name'          => $this->name,
        ];

        $path = 'http://' . env('SYC_ENGINE_HOST', 'localhost') . ':5555' . '/connect/authorize';

        $result = $this->helper->call($path,$params);

        if (!empty($result['type']) && $result['type'] == 'api_error') { //in_array($result['type'],['api_error','oauth']
            throw new SyncException($result['message']);
        }

        return $result['account_id'];
    }

}