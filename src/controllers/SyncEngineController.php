<?php
namespace Usedesk\SyncEngineIntegration\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Usedesk\SyncEngineIntegration\helpers\SyncEngineHelper;
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

            $channel = $this->getChannel($attributes['to'][0]['email']);

            $client_id = dispatch_now(new \App\Jobs\Client\FindOrCreateClient(
                $channel->company_id,
                'email',
                [
                    'email' => $attributes['from'][0]['email'],
                    'client' => ['company_id' => $channel->company_id]
                ]
            ));

            if (!$ticket_id = $this->findTicketById($attributes['body'], $attributes['thread_id'])) {
                $ticket = dispatch_now(new App\Jobs\Ticket\FindOrCreateTicket(
                    $channel->company_id,
                    [ //owner
                        'type' => 'App\Models\Client\Client', //owner
                        'id' => $client_id,
                    ],
                    $channel->id,
                    'email', //channel type
                    $attributes['thread_id'],
                    $attributes['subject'],
                    $data['date'] ?? date("Y-m-d H:i:s")
                ));

                $ticket_id = $ticket->id;
            }

            dispatch(new \App\Jobs\Comment\AddComment(
                $channel->company_id,
                $ticket_id,
                0, //user_id
                [
                    'type' => 'App\Models\Client\Client', //owner
                    'id' => $client_id,
                ],
                [ //request data
                    'user_type' => 'client',
                    'message_type' => 'public',
                    'message' => $attributes['body'],
                    'subject' => $attributes['subject'],
                    'is_html' => true,
                    'has_file' => !empty($files),
                    'all_data' => $data,

                    'channel_type' => 'email',
                    'channel_id' => $channel->id,
                    'from_client' => [
                        $client_id => $attributes['from'][0]['email']
                    ]
                ],
                $files
                ));
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

    /**
     * @param $email
     * @return mixed
     * @throws \Exception
     */
    protected function getChannel($email)
    {
            $channel = DB::table('email_channels')->where('incoming_email', $email)->first();

            if (!$channel) {
                throw new \Exception('Channel not found - ' . $email);
            }

            return $channel;
    }

    protected function findTicketById($message, $thread_id)
    {
        preg_match('/\<span ticket_id="([0-9]+)">/i', $message, $matches);

        if ($matches) {
            DB::table('tickets')->where('id', $matches[1])->update(['thread_id' => $thread_id]);
            return $matches[1];
        }

        return null;
    }

}
