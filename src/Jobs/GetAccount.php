<?php declare(strict_types=1);

namespace Freshplan\Sync\Jobs;

use Usedesk\SyncIntegration\Service\SyncEngineService;
use Usedesk\SyncIntegration\Resources\AccountCollection;

use App\Jobs\AbstractJob;

class GetAccounts extends AbstractJob
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

    public function getAccounts(): JsonResponse
    {
        // $company_id = $this->currentCompany_id;
        // // json_decode(file_get_contents($this->syncService . '/accounts'));

        // $accounts = $this->service->getAccounts($company_id);

        // return new AccountCollection($accounts,$this->admin);
    }
}
    