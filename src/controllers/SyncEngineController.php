<?php
namespace Usedesk\SyncEngineIntegration\Controllers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Faker\Provider\fr_FR\Company;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Usedesk\SyncEngineIntegration\Services\SyncEngineEmail;

class SyncEngineController extends Controller
{
    public function __construct()
    {

    }
    public function createChannel(Request $request){
        header('Access-Control-Allow-Origin: *');
        $response = [];
        $syncRequest = [
            'company_id'=> $request->has('company_id')?$request->get('company_id'):null,
            'channel_id'=> $request->has('channel_id')?$request->get('channel_id'):null,
            'sync_engine_id'=> $request->has('sync_engine_id')?$request->get('sync_engine_id'):null,

            'imap_host'=> $request->has('imap_host')?$request->get('imap_host'):null,
            'imap_port'=> $request->has('imap_port')?$request->get('imap_port'):null,
            'imap_username'=> $request->has('imap_username')?$request->get('imap_username'):null,
            'imap_password'=> $request->has('imap_password')?$request->get('imap_password'):null,

            'smtp_host'=> $request->has('smtp_host')?$request->get('smtp_host'):null,
            'smtp_username'=> $request->has('smtp_username')?$request->get('smtp_username'):null,
            'smtp_password'=> $request->has('smtp_password')?$request->get('smtp_password'):null,
            'smtp_port'=> $request->has('smtp_port')?$request->get('smtp_port'):null,
        ];
        if($request->has('company_id') && $request->has('channel_id') && $request->has('sync_engine_id')){
           $channel = new \SyncEngineChannel($syncRequest);
            $channel->save();
            $response = ['sync_channel_id'=>$channel->id];
        }
        return response(json_encode($response), 200)
            ->header('Content-Type', 'application/json');
    }
    public function syncEngine(Request $request){
        header('Access-Control-Allow-Origin: *');
        $input = $request->all();
        foreach($input as $item){
            if(isset($item['object']) && $item['object'] == "message" ){
                $attributes = $item['attributes'];
                $account_id = 0;
                if(isset($attributes['account_id'])){
                    $account_id = $attributes['account_id'];
                }
                if($account_id) {
                    $channel = SyncEngineChannel::where('sync_engine_id', $account_id)->first();
                    $this->createTicketFromSync($channel,$attributes);
                    continue;
                }
                else{
                    continue;
                }
            }
        }
        return 0;
    }

    public function saveFromSyncEngine(Request $request){
        header('Access-Control-Allow-Origin: *');
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
    public function createTicketFromSync($channel,$attributes,$is_inbound = 0){
        try {
            $message = "";
            if (isset($attributes['body'])) {
                $message = $attributes['body'];
            }
            $subject = "";
            if (isset($attributes['subject'])) {
                $subject = $attributes['subject'];
            }
            $id = "";
            if (isset($attributes['id'])) {
                $id = $attributes['id'];
            }
            $from = ['name' => "", 'email' => ""];
            if (isset($attributes['from']) && isset($attributes['from'][0])) {
                $from['email'] = $attributes['from'][0]['email'];
                $from['name'] = $attributes['from'][0]['name'];
            }
            $account_id = 0;
            if (isset($attributes['account_id'])) {
                $account_id = $attributes['account_id'];
            }
            $thread_id = 0;
            if (isset($attributes['thread_id'])) {
                $thread_id = $attributes['thread_id'];
            }
            $date = Carbon::now();
            if (isset($attributes['date'])) {
                $date = Carbon::createFromTimestamp($attributes['date']);
            }

            $sync_comment = DB::table('sync_engine_ticket_comments')->where('sync_engine_id', $id)->first();
            if ($sync_comment) {
                return false;
            } else {
                $is_outgoing = false;
                if ($from['email'] == $channel->imap_username) {
                    $is_outgoing = true;
                }

//                if (!$is_outgoing) {
//                    $company_id = $channel->company_id;
//                    $client = Client::select('clients.id')
//                        ->join('client_emails', 'client_emails.client_id', '=', 'clients.id')
//                        ->where('clients.company_id', '=', $company_id)
//                        ->where('client_emails.email', $from['email'])
//                        ->first();
//
//                    //создание клиента если его нет
//                    if (!$client) {
//
//                        $client = Client::create(['name' => (empty($from['email'])) ? $from['email'] : $from['name'], 'company_id' => $company_id]);
//
//                        ClientEmail::create(['email' => $from['email'], 'client_id' => $client->id]);
//
//                        if (CompanyIntegration::boolCheck(Integration::TYPE_FULLCONTACT, $client->company_id)) {
//
//                            \UseDesk\Fullcontact\Fullcontact::socialsByEmail($client->id);
//
//                        }
//
//                    }
//                }
                $thread = DB::table('sync_engine_tickets')->where('thread_id', $thread_id)->first();
                $ticket_id = 0;
                if ($thread) {
                    $ticket_id = $thread->ticket_id;
                }

                if (!$ticket_id) {
                    $ticket = new Ticket(['channel' => Ticket::CHANNEL_EMAIL]);
                    $ticket->fill(['email_channel_id' => $channel->id, 'subject' => $subject]);
                    if (!$is_outgoing && $client->id) {
                        $ticket->client_id = $client->id;
                    }
                    $ticket->status_id = TicketStatus::getByKey(TicketStatus::SYSTEM_NEW)->id;
                    $ticket->priority = Ticket::PRIORITY_MEDIUM;
                    $ticket->type = Ticket::TYPE_QUESTION;
                    $ticket->email_channel_subject = $subject;
                    $ticket->email_channel_email = $from['email'];
                    $ticket->company_id = $company_id;
                    $ticket->setStatusUpdatedAt($date);
                    $ticket->last_updated_at = $date;
                    $ticket->published_at = $date;
                    $ticket->additional_id = "sync";
                    $ticket->save();
                    $ticket_id = $ticket->id;
                    DB::table('sync_engine_tickets')->insert([
                        'ticket_id' => $ticket_id,
                        'thread_id' => $thread_id
                    ]);
                }

                $query = [
                    'type' => 'public',
                    'message' => $message,
                    'ticket_id' => $ticket_id,
                    'published_at' => $date,
                ];
                if (!$is_outgoing && isset($client)) {
                    $query['from'] = "client";
                    $query['client_id'] = $client->id;
                } else {
                    $user = User::where('company_id', $channel->company_id)->first();
                    $query['from'] = "user";
                    $query['user_id'] = $user->id;
                }
                $ticketComment = new TicketComment($query);
                $ticketComment->save();
                DB::table('sync_engine_ticket_comments')->insert([
                    'ticket_id' => $ticket_id,
                    'comment_id' => $ticketComment->id,
                    'sync_engine_id' => $id
                ]);

                if(isset($attributes['to']) && count($attributes['to'])>1){
                    foreach($attributes['to'] as $item){
                        if(isset($item[1]) && $channel->imap_username !== $item[1]){
                            TicketCommentCopyEmail::saveEmailCopy($item[1], TicketCommentCopyEmail::TYPE_CC, $ticketComment->id);
                        }
                    }
                }
                if(isset($attributes['cc']) && count($attributes['cc'])>1){
                    foreach($attributes['cc'] as $item){
                        if(isset($item[1]) && $channel->imap_username !== $item[1]){
                            TicketCommentCopyEmail::saveEmailCopy($item[1], TicketCommentCopyEmail::TYPE_CC, $ticketComment->id);
                        }
                    }
                }
                if(isset($attributes['bcc']) && count($attributes['bcc'])>1){
                    foreach($attributes['bcc'] as $item){
                        if(isset($item[1]) && $channel->imap_username !== $item[1]){
                            TicketCommentCopyEmail::saveEmailCopy($item[1], TicketCommentCopyEmail::TYPE_BCC, $ticketComment->id);
                        }
                    }
                }
                if (isset($attributes['files'])) {
                    $files = $attributes['files'];
                    foreach ($files as $file) {
                        if (isset($file['id'])) {
                            $file_id = $file['id'];
                            $url = 'http://' . $account_id . '@188.93.209.204:15555/files/' . $file_id . '/download';
                            DB::table('ticket_comment_files')->insert([
                                'ticket_comment_id' => $ticketComment->id,
                                'file' => $url
                            ]);
                        }
                    }

                }
            }
        }
        catch(\Exception $e){
            Log::alert($e);
            return false;
        }
    }
}
