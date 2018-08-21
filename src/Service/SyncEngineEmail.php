<?php declare(strict_types=1);

namespace Usedesk\SyncIntegration\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEngineEmail 
{
    public $ticket = null;
    public $comment = null;
    public $channel = null;
    public $is_new_ticket = true;
    public $is_outgoing = false;
    public $client = null;
    public $thread_exist = false;
    public $messages_ids = [];
    public $reply_to = null;
    public $folderName = null;
    public $folderDisplayName = null;
    protected $to = null;
    protected $from_email = '';
    protected $from_name = '';
    protected $company_id = null;
    protected $thread_id = null;
    protected $sync_engine_id = null;
    protected $account_id = null;

    public function __construct($params,$channel)
    {
        if(is_array($params)){
            $this->fillFromArray($params);
        }
        else{
            $this->fill($params);
        }
        $this->setChannel($channel);
        $this->setCompanyId($channel->company_id);
        if(!$this->checkSyncMessage()){
            $this->is_outgoing = $this->checkOutgoing();
        } 
        $this->client = $this->getClient();
        $ticket_id = $this->checkThread();
        if($ticket_id){
            $this->is_new_ticket = false;
            $this->ticket = new SyncEngineTicket($params, $ticket_id);
            $this->thread_exist = true;
        }
        else{
            $this->ticket = new SyncEngineTicket($params);
        }
//    $this->comment = $this->createSyncComment($params);
        $date = Carbon::now();
        if (isset($params['date'])) {
            if(isset($params['date']['$date'])){
                $timestamp = $params['date']['$date']/1000;
                $date = Carbon::createFromTimestamp($timestamp);
            }
            else if(is_string($params['date'])){
                $date = Carbon::createFromTimestamp($params['date']);
            }
        }
        $this->ticket->setLastUpdatedAt($date);
        $this->ticket->setPublishedAt($date);
//    $this->comment->setPublishedAt($date);
    }
    protected function fillFromArray($params){
        isset($params['account_id']) && $this->setAccountId($params['account_id']);
        isset($params['id']) && $this->setSyncEngineId($params['id']);
        isset($params['thread_id']) && $this->setThreadId($params['thread_id']);
        if(isset($params['message_ids'])){
            $this->messages_ids = $params['message_ids'];
        }
        if (isset($params['from']) && isset($params['from'][0])) {
            $this->setFromEmail($params['from'][0]['email']);
            $this->setFromName($params['from'][0]['name']);
        }
        isset($params['to']) && $this->setTo($params['to']);

        if(isset($params['folder_name'])){
            $this->folderName = $params['folder_name'];
        }
        if(isset($params['folder_display_name'])){
            $this->folderDisplayName = $params['folder_display_name'];
        }

    }
    protected function fill($params){
        isset($params->account_id) && $this->setAccountId($params->account_id);
        isset($params->id) && $this->setSyncEngineId($params->id);
        isset($params->thread_id) && $this->setThreadId($params->thread_id);
        if(isset($params->message_ids)){
            $this->messages_ids = $params->message_ids;
        }
        if (isset($params->from) && isset($params->from[0])) {
            $this->setFromEmail($params->from[0]['email']);
            $this->setFromName($params->from[0]['name']);
        }
        isset($params->to) && $this->setTo($params->to);
        if(isset($params->folder_name)){
            $this->folderName = $params->folder_name;
        }
        if(isset($params->folder_display_name)){
            $this->folderDisplayName = $params->folder_display_name;
        }
    }
    protected function checkThread(){
        $thread_id = $this->getThreadId();
        $thread =  DB::table('sync_engine_tickets')->where('thread_id', $thread_id)->first();
        if($thread){
            return $thread->ticket_id;
        }
        return 0;
    }
    protected function checkSyncMessage(){
        $message_id = $this->getSyncEngineId();
        $sync_comment = DB::table('sync_engine_ticket_comments')->where('sync_engine_id', $message_id)->first();
        if($sync_comment){
            return $sync_comment->comment_id;
        }
        return 0;
    }
    protected function checkSyncMessageById($id){
        $message_id = $id;
        $sync_comment = DB::table('sync_engine_ticket_comments')->where('sync_engine_id', $message_id)->first();
        if($sync_comment){
            return $sync_comment->comment_id;
        }
        return 0;
    }
    protected function checkOutgoing(){
        if ($this->from_email == $this->channel->imap_username) {
            return true;
        }
        return false;
    }

    protected function getClient(){
        $company_id = $this->getCompanyId();
        $from_email = $this->getFromEmail();
        $from_name =  $this->getFromName();
        $this->reply_to = $from_email;
        if($this->is_outgoing){
            $to = $this->getTo();
            if($to && is_array($to)){
                $from_email = $to[0]['email'];
                $from_name =  $to[0]['name'];
                $this->reply_to = $to[0]['email'];
            }
        }
        $client = null;
//    $client = \Client::select('clients.id')
//        ->join('client_emails', 'client_emails.client_id', '=', 'clients.id')
//        ->where('clients.company_id', '=', $company_id)
//        ->where('client_emails.email', $from_email)
//        ->first();
//    //создание клиента если его нет
//    if (!$client) {
//        $client = \Client::create(['name' => (empty($from_email)) ? $from_email : $from_name, 'company_id' => $company_id]);
//        \ClientEmail::create(['email' => $from_email, 'client_id' => $client->id]);
//    }
        return $client;
    }

    protected function createThread(){
        $id = DB::table('sync_engine_tickets')->insertGetid([
            'ticket_id' => $this->ticket->getTicketId(),
            'thread_id' => $this->getThreadId()
        ]);
        return $id;
    }

    protected function createSaveSyncMessageId($ticket,$comment){
        DB::table('sync_engine_ticket_comments')->insert([
            'ticket_id' => $ticket->id,
            'comment_id' => $comment->id,
            'sync_engine_id' => $this->getSyncEngineId()
        ]);
    }

    protected function SaveCopy(){
        $to = $this->comment->getTo();
        $cc = $this->comment->getCc();
        $bcc = $this->comment->getBcc();
        if($to && count($to)>1){
            foreach($to as $item){
                if(isset($item['email']) && $this->channel->imap_username !== $item['email']){
                    \TicketCommentCopyEmail::saveEmailCopy($item['email'], \TicketCommentCopyEmail::TYPE_CC, $this->comment->getCommentId());
                }
            }
        }
        if($cc){
            foreach($cc as $item){
                if(isset($item['email']) &&  $this->channel->imap_username !== $item['email']){
                    \TicketCommentCopyEmail::saveEmailCopy($item['email'], \TicketCommentCopyEmail::TYPE_CC, $this->comment->getCommentId());
                }
            }
        }
        if($bcc){
            foreach($bcc as $item){
                if(isset($item['email']) &&  $this->channel->imap_username !== $item['email']){
                    \TicketCommentCopyEmail::saveEmailCopy($item['email'], \TicketCommentCopyEmail::TYPE_BCC, $this->comment->getCommentId());
                }
            }
        }
    }
    protected function createSyncTicket($params,$ticket_id = 0){
        $sync_ticket = new SyncEngineTicket($params);
        $sync_ticket->setChannel(\Ticket::CHANNEL_EMAIL);
        $sync_ticket->setAdditionalId('sync');
        $sync_ticket->setStatusId(\TicketStatus::getByKey(\TicketStatus::SYSTEM_NEW)->id);
        $sync_ticket->setPriority(\Ticket::PRIORITY_MEDIUM);
        $sync_ticket->setType(\Ticket::TYPE_QUESTION);
        $sync_ticket->setCompanyId($this->company_id);
        if($ticket_id){
            $sync_ticket->setTicketId($ticket_id);
        }
        return $sync_ticket;
    }
    protected function createUsedeskTicket(){
        $ticket = new \Ticket(['channel' => $this->ticket->geChannel()]);
        $ticket->fill(['email_channel_id' => $this->channel->id, 'subject' => $this->ticket->getSubject()]);
        if (!$this->is_outgoing && isset($this->client->id)) {
            $ticket->client_id =  $this->client->id;
        }
        else if( isset($this->client->id)){
            $ticket->client_id =  $this->client->id;
        }
        $ticket->status_id = $this->ticket->getStatusId();
        $ticket->priority = \Ticket::PRIORITY_MEDIUM;
        $ticket->type = \Ticket::TYPE_QUESTION;
        $ticket->email_channel_id = $this->channel->id;
        $ticket->email_channel_subject = $this->ticket->getSubject();
        $ticket->email_channel_email = $this->reply_to;
        $ticket->company_id = $this->getCompanyId();
        $ticket->last_updated_at = $this->ticket->getLastUpdatedAt();
        $ticket->published_at = $this->ticket->getPublishedAt();
        $ticket->additional_id =  $this->ticket->getAdditionalId();
        $ticket->save();
        return $ticket;
    }

    protected function createNewUsedeskTicket(){
        $ticketData = [
            'subject'=> $this->ticket->getSubject(),
            'company_id'=> $this->getCompanyId(),
            'client_id'=>1,
            'status_id' => 1,
            'priority' => 'medium',
            'type' => 'question',
        ];
        $job = new \App\Jobs\Ticket\CreateSyncTicket($ticketData);
        return dispatch_now($job);
    }
    protected function getUsedeskTicket(){
        $ticket =\Ticket::where('id',$this->ticket->getTicketId())->first();
        return $ticket;
    }
    protected function createSyncComment($params){
        $sync_comment = new SyncEngineComment($params);
        $sync_comment->setType('public');
        $sync_comment->setTicketId($this->ticket->getTicketId());
        if (!$this->is_outgoing && isset($this->client)) {
            $sync_comment->setFrom("client");
            $sync_comment->setClientId($this->client->id);
        }
        else{
            $user = \User::where('company_id', $this->getCompanyId())->where('email',$this->getFromEmail())->first();
            if(!$user){
                $user = \User::where('company_id', $this->getCompanyId())->first();
            }
            $sync_comment->setFrom("user");
            $sync_comment->setUserId($user->id);
        }
        return $sync_comment;
    }
    protected function createUsedeskComment(){
        $query = [
            'type' => 'public',
            'message' => $this->comment->getMessage(),
            'ticket_id' =>$this->ticket->getTicketId(),
            'published_at' => $this->comment->getPublishedAt(),
            'from'=>$this->comment->getFrom()
        ];
        if($query['from']=='client'){
            $query['client_id']= $this->comment->getClientId();
        }
        else{
            $query['user_id']= $this->comment->getUserId();
        }
        $ticketComment = new \TicketComment($query);

        $ticketComment->save();
        return $ticketComment;
    }
    protected function saveCommentFile(){
        $files = $this->comment->getFiles();
        if ($files) {
            foreach ($files as $file) {
                if (isset($file['id'])) {
                    $file_id = $file['id'];
                    $url = 'http://' . $this->account_id . '@188.93.209.204:15555/files/' . $file_id . '/download';
                    $query = [
                        'ticket_comment_id' => $this->comment->getCommentId(),
                        'file' => $url
                    ];
                    if(isset($file['filename'])){
                        $query['file_name']=$file['filename'];
                    }
                    DB::table('ticket_comment_files')->insert($query);
                }
            }

        }
    }

    public function updateTicketStatus($status){
        DB::table('ticket')->where('id',$this->ticket->getTicketId())->update([
            'status_id'=>$status
        ]);
    }

    public function saveEmail(){
        if($this->checkSyncMessage()){
            return true;
        }
        if($this->is_outgoing && $this->thread_exist && count($this->messages_ids)>1){
            return true;
        }
        if(!$this->folderName && !$this->folderDisplayName && $this->is_outgoing){
            return true;
        }
        if(!$this->folderName || $this->folderName == 'sent'){
            if($this->messages_ids){
                foreach($this->messages_ids as $item){
                    $comment_id = $this->checkSyncMessageById($item);
                    if($comment_id){
                        DB::table('sync_engine_ticket_comments')->where('comment_id',$comment_id)->update([
                            'sync_engine_id' =>  $this->getSyncEngineId()
                        ]);
                        return true;
                    }
                }
            }
        }
        if($this->is_new_ticket || !$this->ticket->getTicketId()){
            $ticket = $this->createUsedeskTicket();
            $this->ticket->setTicketId($ticket->id);
            $thread = $this->createThread();
            $this->setThreadId($thread);
        }
        else{
            $ticket = $this->getUsedeskTicket();
            if($ticket){
//              $ticket->status_id =  \TicketStatus::getByKey(\TicketStatus::SYSTEM_OPENED)->id;
                $ticket->status_id =  1;
            }
        }
        $comment = $this->createUsedeskComment();
        $this->comment->setCommentId($comment->id);
        $this->saveCopy();
        $this->saveCommentFile();
        $this->createSaveSyncMessageId($ticket,$comment);
        if(!$this->is_new_ticket) {
            $ticket->last_updated_at = Carbon::now();
        }
        $ticket->save();
        if($this->folderDisplayName){
            $ticket->addTags([$this->folderDisplayName]);
        }
        \Trigger::checkAndRunAuto($ticket);
        return [
            'ticket'=> $ticket,
            'comment'=> $comment
        ];
    }
    public function getTicket()
    {
        return $this->ticket;
    }
    public function setTicket($val)
    {
        $this->ticket = $val;
    }
    public function getComment()
    {
        return $this->comment;
    }
    public function setComment($val)
    {
        $this->comment = $val;
    }
    public function getFromEmail()
    {
        return $this->from_email;
    }
    public function setFromEmail($val)
    {
        $this->from_email = $val;
    }
    public function getFromName()
    {
        return $this->from_name;
    }
    public function setFromName($val)
    {
        $this->from_name = $val;
    }
    public function getChannel()
    {
        return $this->channel;
    }
    public function setChannel($val)
    {
        $this->channel = $val;
    }

    public function getCompanyId()
    {
        return $this->company_id;
    }
    public function setCompanyId($val)
    {
        $this->company_id = $val;
    }

    public function getThreadId()
    {
        return $this->thread_id;
    }
    public function setThreadId($val)
    {
        $this->thread_id = $val;
    }

    public function getSyncEngineId()
    {
        return $this->sync_engine_id;
    }
    public function setSyncEngineId($val)
    {
        $this->sync_engine_id = $val;
    }
    public function getAccountId()
    {
        return $this->account_id;
    }
    public function setAccountId($val)
    {
        $this->account_id = $val;
    }
    public function getTo()
    {
        return $this->to;
    }
    public function setTo($val)
    {
        $this->to = $val;
    }

}