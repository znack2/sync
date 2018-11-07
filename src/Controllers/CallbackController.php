<?php declare(strict_types=1);

namespace Freshplan\Sync\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;

use Freshplan\Sync\\Exceptions\SyncException;

use App\Helpers\System\{
	ResponseHelper,
	CurlHelper
};

use App\Jobs\Channel\{SetAccountChannel};

class CallbackController
{
    protected $helper;
    protected $response;

    public function __construct()
    {
        $this->helper = new CurlHelper;
        $this->response = new ResponseHelper;
    }

    /**
     * Создать email канал
     * @param string $state required Название канала
     * @param string $auth_code required Email для входящей почты
     */
    public function callback(Request $request): JsonResponse 
    {
    	$code        = $request->get('code');
    	$channel_id  = $request->get('state');

		// $channel = dispatch_now(new GetChannel(
		// 	$company_id,
		// 	$channel_id
  //       ));

        $channel = DB::table('email_channels')->find($channel_id);

		$params = [
            'auth_code'     => $code,
            'provider'      => $channel->provider,
            'email_address' => $channel->incoming_email,
            'name'          => $channel->name,
        ];

        $path = 'http://' . env('SYC_ENGINE_HOST', 'localhost') . ':5555' . '/connect/authorize';

        $result = $this->helper->call($path,$params);

        if (!empty($result['type']) && $result['type'] == 'api_error') { //in_array($result['type'],['api_error','oauth']
            throw new SyncException($result['message']);
        }

        dispatch(new SetAccountChannel(
			$channel->company_id,
			$channel->id,
			$result['account_id']
        ));

        $response['id']     = $channel->id;
        $response['message']= 'channel added successfully.';

        return $this->response->sendResponse($response);
    }
}


