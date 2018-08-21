<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Usedesk\SyncIntegration\Service\SyncEngineService;

class GetAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $helper;

    protected $params;
    protected $data;

    public function __construct(array $params, array $data)
    {
        $this->params = $params;
        $this->data = $data;
        $this->helper = new SyncEngineService;
    }

    public function getAccounts()
    {
        $result = json_decode(file_get_contents($this->syncService . '/accounts'));

        // return new AccountCollection($result);
    }
}
    