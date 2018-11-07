<?php

namespace Usedesk\SyncIntegration\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

use App\Models\Channel\EmailChannel;

use App\Jobs\Client\{FindOrCreateClient};
use App\Jobs\Comment\{AddComment};
use App\Jobs\Ticket\{
    FindOrCreateTicket, 
    AddTicketContact
}; //CheckDouble
use App\Helpers\Project\OwnerHelper;

class SyncController
{
	 /**
     */
    public function create(Request $request)
    {
        Log::info('-------start-----');

        // parse_str($request->getContent(),$requestData);
        // $requestData = $request->all();

        if($requestData = json_decode($request->getContent(), true)){

            foreach ($requestData as $item) {
                if (!empty($item['attributes']['object']) && $item['attributes']['object'] === 'message' || !empty($item['attributes']['event']) && $item['attributes']['event'] === 'create'){

                    // if(!Cache::has($item['attributes']['subject'])){
                    //     Cache::put($item['attributes']['subject'], $item['attributes']['subject'], 10);
                        $this->prepare($item['attributes']);
                    // }
                }
            }



            // $need = ['body', 'from', 'account_id', 'thread_id', 'snippet', 'id', 'subject'];

            // foreach ($need as $key) {
            //     $row = $item['attributes'][$key] ?? null;

            //     if ($row === null) {
            //         continue;
            //     }

            //     if (is_array($key) or is_object($key)) {
            //         $data[$key] = json_encode($row);
            //         continue;
            //     }

            //     $data[$key] = $row;
            // }

            // Log::info($data);
        }

        
        Log::info('-------done-----');

        return 'ok';
    }

    private function prepare($attributes)
    {
        return $this->setupMessage(
            // $attributes['id'],
            // $attributes['object'],
                $attributes['account_id'],
            $attributes['subject'],
            $attributes['thread_id'],
            // $attributes['snippet'],
            $attributes['body'],
            // $attributes['body_content'],
            // $attributes['event'],
                $attributes['from'],
            // $attributes['reply_to'],
                $attributes['to'],
            // $attributes['cc'],
            // $attributes['bcc'],
                $attributes['files'],
                $attributes['date']
            // $attributes['events'],
                // $attributes['body_quotes']
            // $attributes['unread'],
            // $attributes['starred']
        );
    }


    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    private function setupMessage(
        // string $id = null,
        // string $object = null,
            string $account_id = null,
            string $subject = null,
            string $thread_id = null,
        // string $snippet = null,
            string $body = null,
        // string $body_content = null,
        // string $event = null,

            array $from = [],
        // array $reply_to = [],
            array $to = [],
        // array $cc = [],
        // array $bcc = [],
            array $files = [],
            array $date = []
        // array $events = [],

            // bool $body_quotes = false
        // bool $unread = false,
        // bool $starred= false
    )
    {
        // if($object == 'message'){
        //     Log::info($id);
        // }else if($object == 'thread'){

        // }

        $date = $this->setDate($date['$date']);

        $channelData = $this->setChannel($account_id, $to[0]['email']);
        
        $clientData = $this->setClient($channelData['company_id'],$from);

        $files = $this->setFiles($files);

        $ownerData = $this->assignTicketToOwner($clientData);

        // if (dispatch_now(new CheckDouble($channel->company_id, $client_id, $subject, $date))) {
        //     return false;
        // }
        // dd($owner);

        $ticketData = $this->setTicket(
            $channelData['company_id'],
            $clientData,
            $channelData,
            $thread_id,
            $subject,
            // $body_quotes,
            $body,
            $ownerData,
            $date,
            $files
        );

        $this->sendComment(
            $channelData['company_id'],
            $ticketData,
            $ownerData,
            $clientData,
            $channelData,
            $body,
            $files
        );

        // dispatch(new AddTicketContact(
        //     $company_id,
        //     $ticket,
        //     $comment_id,
        //     $client
        // ));

        // event(new SyncCreated(
        //     $company_id,
        //     $ticket_id,
        //     $comment_id
        // ));

        return $account_id;
    }

    private function assignTicketToOwner(array $clientData = []): array
    {
        $ownerHelper = new OwnerHelper;

        $result['type']  = $ownerHelper->getOwner('client');//trigger//bot
        $result['id']    = $clientData['id'];

        return $result;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     */
    // private function prepare(array $attributes)
    // {
    //     $data = [];

    //     $need = ['body', 'from', 'account_id', 'thread_id', 'snippet', 'id', 'subject'];

    //     foreach ($need as $key) {
    //         $row = $attributes[$key] ?? null;

    //         if ($row === null) {
    //             continue;
    //         }

    //         if (is_array($key) or is_object($key)) {
    //             $data[$key] = json_encode($row);
    //             continue;
    //         }

    //         $data[$key] = $row;
    //     }

    //     return $data;
    // }

    /**
     * Handle the event.
     *
     */
    private function setDate(string $date = null): string 
    {
        // if (!empty($date)) {
        //     $data = date('Y-m-d G:i:s',$date);
        //     // $data = gmdate("Y-m-d\TH:i:s\Z", $date)
        //     // $data = date("Y-m-d H:i:s", $date);
        // } else {
            $data = date("Y-m-d H:i:s"); //2018-10-26 21:29:23
        // }
        return $data;
    }

    /**
     * Handle the event.
     *
     */
    private function setChannel(string $account_id, string $email): array
    {
        $channel = DB::table('email_channels')
            ->where('incoming_email', $email)
            // ->where('account_id', $account_id)
            ->first();

        $channelData = [
            'company_id'    => $channel->company_id,
            'type'          => EmailChannel::class,
            'id'            => $channel->id
        ];

        return $channelData;
    }

    /**
     * Handle the event.
     *
     */
    private function setFiles(array $files): array
    {
        $result = [];

            if (isset($files)) {
                foreach ($files as $file) {
                    if (isset($file['id'])) {
                        $file_id = $file['id'];

                        $url = env('SYC_ENGINE_HOST', 'localhost') . ':5555';

                        $file_data = [
                            'url' => 'http://' . ($data['account_id']) . '@' . $url .'/files/' . $file_id . '/download',
                            'filename' => $file['filename'],
                        ];

                        if (!empty($file['content_id'])) {
                            $file_data['content_id'] = $file['content_id'];
                        }

                        $result[] = $file_data;
                    }
                }
            }
        return $result;
    }

    /**
     * Handle the event.
     *
     */
    private function setClient(int $company_id,array $from): array
    {
        $params = [
            'contact'       => $from[0]['email'],
            'clientName'    => $from[0]['name'] ?? ''
        ];

        $client = dispatch_now(new FindOrCreateClient(
            $company_id,
            'email',
            $params
        ));

        return $client;
    }

    /**
     * Handle the event.
     *
     */
    private function sendComment(int $company_id, array $ticketData = [], array $owner, array $client, array $channel, string $body, array $files = [])
    {
        preg_match('/\<span ticket_id=\\"([0-9]+)\\"><\/span>/i', $body, $res);

        $commentData = [
            'type'      => 'public',
            'body'      => $res ? str_replace($res[0],'',$body) : $body,
            'is_first'  => $ticketData['is_first'],
        ];

        $comment = dispatch_now(new AddComment(
            $company_id,
            $currentUser_id = null,
            $owner,
            $channel,
            $client,
            $commentData,
            $ticketData,
            $files,
            $tags = [],
            $fields = []
        ));

        return $comment;
    }

    /**
     * Handle the event.
     *
     */
    private function setTicket(int $company_id, array $client, array $channel, string $thread_id = null, string $subject, 
        // bool $body_quotes = false, 
        string $body, array $owner = [], $date, array $files): array
    {
        $ticket_id = preg_match('/\<span ticket_id=\\"([0-9]+)\\">/i', $body, $ticketData) ? $ticketData[1] : null;

        Log::info('-------TICKET-----',$ticket_id);

        //TODO: remove repository
        // if ($matches) {
        //     DB::table('tickets')->where('id', $matches[1])->update(['thread_id' => $attributes['thread_id']]);
        //     return $matches[1];
        // }

        $requestData['title'] = $subject;
        // $requestData['body_quotes'] = $body_quotes;

        $ticket = dispatch_now(new FindOrCreateTicket(
            $company_id,
            $currentUser_id = null,
            $owner,
            $client,
            $channel,
            $thread_id,
            $ticket_id,
            $requestData,
            $date,
            (bool) !empty($files)
        ));

        return $ticket;
    }

}