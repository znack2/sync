<?php
namespace Usedesk\SyncEngineIntegration\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use usedesk\SyncEngineIntegration\helpers\SyncEngineHelper;
use Usedesk\SyncEngineIntegration\Services\SyncEngineEmail;

class SyncEngineController
{

    private $addr;

    public function __construct()
    {
        $this->addr =  SyncEngineHelper::getAddr();
    }

    public function createChannel(Request $request){
        if($request->has('name') and $request->has('email_address') and $request->has('password')) {
            $response = SyncEngineHelper::createAccount($request->all());
        } else {
            $response = ['error' => 'data error'];
        }

        return response(json_encode($response), 200)
            ->header('Content-Type', 'application/json');
    }


    public function syncEngine(Request $request){
        $input = $request->all();
        foreach($input as $item){
            $object = $item['object'] ?? null;
            $event = $item['event'] ?? null;

            if ($object != 'message' or $event != 'create') {
                continue;
            }

            $this->createTicketFromSync($item['attributes']);
        }
        return 'ok';
    }

    public function saveFromSyncEngine(Request $request){
        $input = $request->all();
        $attributes = [];
        $account_id = null;
        $message_ids = [];
        $folderName = null;
        $folderDisplayName = null;
        foreach($input as $item){
            if(isset($item['object']) && $item['object'] == "message" ){
                $attributes = $item['attributes'];
                $account_id = 0;
                if(isset($attributes['account_id'])){
                    $account_id = $attributes['account_id'];
                }
            }

            if(isset($item['attributes']) && isset($item['attributes']['message_ids']) && !count($message_ids)){
                $message_ids = $item['attributes']['message_ids'];
            }
            if(isset($item['attributes']) && isset($item['attributes']['folder'])){
                $folder = $item['attributes']['folder'];

                if(isset($folder['name']) && !$folderName){
                    $folderName = $folder['name'];
                }

                if(isset($folder['display_name']) && !$folderDisplayName){
                    $folderDisplayName = $folder['display_name'];
                }
            }
        }
        if($account_id) {
            $channel = SyncEngineChannel::where('sync_engine_id', $account_id)->first();
            if(!$channel){
                return false;
            }
            $attributes['message_ids']=$message_ids;
            $attributes['folder_name']=$folderName;
            $attributes['folder_display_name']=$folderDisplayName;
            try {
                $syncEmail = new SyncEngineEmail($attributes, $channel);
                if ($syncEmail) {
                    $result = $syncEmail->saveEmail();
                }
            }
            catch(\Exception $e){
                Log::alert($e);
            }
        }
        return response(json_encode($attributes), 200)
            ->header('Content-Type', 'application/json');
    }

    public function createTicketFromSync($attributes) {
        try {
            $data = [];

            $need = ['body', 'from', 'account_id', 'thread_id', 'snippet', 'id', 'subject'];

            foreach ($need as $key) {
                $row = $attributes[$key] ?? null;

                if ($row === null) {
                    continue;
                }

                if (is_array($key) or is_object($key)) {
                    $data[$key] = json_encode($row);
                    continue;
                }

                $data[$key] = $row;
            }

            if (!empty($attributes['date'])) {
                $data['date'] = date("Y-m-d H:i:s", $attributes['date']['$date']);
            }

            $files = [];

            if (isset($attributes['files'])) {
                foreach ($attributes['files'] as $file) {
                    if (isset($file['id'])) {
                        $file_id = $file['id'];
                        $files[] = 'http://' . ($data['account_id']) . '@' . $this->addr .'/files/' . $file_id . '/download';
                    }
                }
            }

            dispatch(new \App\Jobs\Ticket\CreateTicketSyncEngine($attributes, $files));
        }
        catch(\Exception $e){
            Log::alert($e);
            return false;
        }
    }

    public function accounts()
    {
        return json_decode(file_get_contents($this->addr . '/accounts'));
    }

}
