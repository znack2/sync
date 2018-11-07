<?php

namespace Usedesk\SyncIntegration\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class TestController
{
	 /**
     */
    public function create(Request $request)
    {
        Log::info('-------start-----');

        if($requestData = json_decode($request->getContent(), true)){

            $requestData = $request->all();

            foreach ($requestData as $item) {
                if (!empty($item['attributes']['object']) && $item['attributes']['object'] === 'message' || !empty($item['attributes']['event']) && $item['attributes']['event'] === 'create'){

                    if(!Cache::has($item['attributes']['subject'])){
                        Cache::put($item['attributes']['subject'], $item['attributes']['subject'], 10);
                        Log::info($item['attributes']['subject']);
                    }
                }
            }
        }

        $input = $request->all();

            $need = ['body', 'from', 'account_id', 'thread_id', 'snippet', 'id', 'subject'];

            foreach ($need as $key) {
                $row = $item['attributes'][$key] ?? null;

                if ($row === null) {
                    continue;
                }

                if (is_array($key) or is_object($key)) {
                    $data[$key] = json_encode($row);
                    continue;
                }

                $data[$key] = $row;
            }

        Log::info('-------done-----');

        return 'ok';
	}
}