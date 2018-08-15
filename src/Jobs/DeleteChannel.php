<?php
namespace Usedesk\SyncIntegration\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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

    }

}