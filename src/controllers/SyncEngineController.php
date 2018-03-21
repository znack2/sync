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

    /**
     * @param Request $request
     * @return mixed
     */
    public function createChannel(Request $request){
        if($request->has('name') and $request->has('email_address') and $request->has('password')) {
            $response = SyncEngineHelper::createAccount($request->all());
        } else {
            $response = ['error' => 'data error'];
        }

        return response(json_encode($response), 200)
            ->header('Content-Type', 'application/json');
    }

    /**
     * @param Request $request
     * @return string
     */
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

    /**
     * @param $attributes
     * @return bool
     */
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
