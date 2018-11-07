<?php declare(strict_types=1);

namespace Freshplan\Sync\Jobs;

use Freshplan\Sync\\Service\SyncEngineService;

use App\Jobs\AbstractJob;

class ActivateChannel extends AbstractJob
{
	private $service;

    protected $params;
    protected $data;

    public function __construct(array $params, array $data)
    {
        $this->params = $params;
        $this->data = $data;
        $this->service = new SyncEngineService;
    }

    public function handle(): JsonResponse
    {
        $data = [];
        
        //if update account
        if (isset($this->requestData['activate'])) {
            $data['reauth'] = true;
        }
    }

}