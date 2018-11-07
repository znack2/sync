<?php declare(strict_types=1);

namespace Freshplan\Sync\Jobs;

use Usedesk\SyncIntegration\Service\SyncEngineService;

use App\Jobs\AbstractJob;

class UpdateChannel extends AbstractJob
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

    }

}