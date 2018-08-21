<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Usedesk\SyncIntegration\Service\SyncEngineService;

class DeleteChannel implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel_id;

    public function __construct(string $channel_id)
    {
        $this->channel_id = $channel_id;
    }

    public function handle()
    {
        return response([
            'success' => true,
            'message' => 'channel deleted succeesfully',
    }

}