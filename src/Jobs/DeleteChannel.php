<?php declare(strict_types=1);

namespace Freshplan\Sync\Jobs;

use App\Helpers\System\CurlHelper;

use Usedesk\SyncIntegration\Exceptions\SyncException;

use App\Jobs\AbstractJob;



class DeleteChannel extends AbstractJob
{
    protected $account_id;
    private $helper;

     /**
     */
    public function __construct(string $account_id)
    {
        $this->account_id = $account_id;
        $this->helper = new CurlHelper;
    }

     /**
     */
    public function handle()
    {
        $path = 'http://' . env('SYC_ENGINE_HOST', 'localhost') . ':5555' . '/account/purge';

        $result = $this->helper->call($path,[],$this->account_id);

        if (!empty($result['type']) && $result['type'] == 'api_error') { //in_array($result['type'],['api_error','oauth']
            throw new SyncException($result['message']);
        }

        return $result;
    }
}