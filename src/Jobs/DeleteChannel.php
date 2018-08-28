<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use Usedesk\SyncIntegration\Service\SyncEngineService;

use App\Jobs\AbstractJob;

class DeleteChannel extends AbstractJob
{
    protected $channel_id;

    public function __construct(string $channel_id)
    {
        $this->channel_id = $channel_id;
    }

    public function handle(): JsonResponse
    {

    }

}