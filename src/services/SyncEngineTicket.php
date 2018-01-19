<?php
/**
 * Created by PhpStorm.
 * User: souri
 * Date: 05.07.2017
 * Time: 10:56
 */
namespace UseDesk\SyncEngineIntegration\services;
use Carbon\Carbon;
use Exception;

class SyncEngineTicket
{
    protected $status_id = null;
    protected $priority = null;
    protected $type = null;
    protected $email_channel_subject = '';
    protected $email_channel_email = '';
    protected $company_id = null;
    protected $last_updated_at = null;
    protected $published_at = null;
    protected $additional_id = "sync";
    protected $ticket_id = null;
    protected $email_channel_id = null;
    protected $subject = '';
    protected $channel = null;

    public function __construct($params, $ticket_id = 0)
    {
        $this->setChannel(\Ticket::CHANNEL_EMAIL);
        $this->setAdditionalId('sync');
        $this->setStatusId(\TicketStatus::getByKey(\TicketStatus::SYSTEM_NEW)->id);
        $this->setPriority(\Ticket::PRIORITY_MEDIUM);
        $this->setType(\Ticket::TYPE_QUESTION);
        $this->setCompanyId($this->company_id);
        if(is_array($params)){
            $this->fillFromArray($params);
        }
        else{
            $this->fill($params);
        }
        if($ticket_id){
            $this->setTicketId($ticket_id);
        }
        $date = Carbon::now();
        $this->setLastUpdatedAt($date);
        $this->setPublishedAt($date);
    }

    protected function fill($data)
    {
        if(is_null($data)) return;
        isset($data->status_id) && $this->setStatusId($data->status_id);
        isset($data->priority) && $this->setPriority($data->priority);
        isset($data->email_channel_subject) && $this->setChannelSubject($data->email_channel_subject);
        isset($data->email_channel_email) && $this->setChannelEmail($data->email_channel_email);
        isset($data->company_id) && $this->setCompanyId($data->company_id);
        isset($data->last_updated_at) && $this->setLastUpdatedAt($data->last_updated_at);
        isset($data->published_at) && $this->setPublishedAt($data->published_at);
        isset($data->ticket_id) && $this->setTicketId($data->ticket_id);
        isset($data->email_channel_id) && $this->setEmailChannelId($data->email_channel_id);
        isset($data->subject) && $this->setSubject($data->subject);
        isset($data->channel) && $this->setChannel($data->channel);
        isset($data->type) && $this->setType($data->type);
    }
    protected function fillFromArray($data)
    {
        if(is_null($data)) return;
        isset($data['status_id']) && $this->setStatusId($data['status_id']);
        isset($data['priority']) && $this->setPriority($data['priority']);
        isset($data['email_channel_subject']) && $this->setChannelSubject($data['email_channel_subject']);
        isset($data['email_channel_email']) && $this->setChannelEmail($data['email_channel_email']);
        isset($data['company_id']) && $this->setCompanyId($data['company_id']);
        isset($data['last_updated_at']) && $this->setLastUpdatedAt($data['last_updated_at']);
        isset($data['published_at']) && $this->setPublishedAt($data['published_at']);
        isset($data['ticket_id']) && $this->setTicketId($data['ticket_id']);
        isset($data['email_channel_id']) && $this->setEmailChannelId($data['email_channel_id']);
        isset($data['subject']) && $this->setSubject($data['subject']);
        isset($data['channel']) && $this->setChannel($data['channel']);
        isset($data['type']) && $this->setType($data['type']);
    }
    public function getStatusId()
    {
        return $this->status_id;
    }
    public function setStatusId($val)
    {
        $this->status_id = $val;
    }

    public function getPriority()
    {
        return $this->priority;
    }
    public function setPriority($val)
    {
        $this->priority = $val;
    }

    public function getType()
    {
        return $this->type;
    }
    public function setType($val)
    {
        $this->type = $val;
    }

    public function getChannelSubject()
    {
        return $this->email_channel_subject;
    }
    public function setChannelSubject($val)
    {
        $this->email_channel_subject = $val;
    }


    public function getChannelEmail()
    {
        return $this->email_channel_email;
    }
    public function setChannelEmail($val)
    {
        $this->email_channel_email = $val;
    }

    public function getCompanyId()
    {
        return $this->company_id;
    }
    public function setCompanyId($val)
    {
        $this->company_id = $val;
    }

    public function getLastUpdatedAt()
    {
        return $this->last_updated_at;
    }
    public function setLastUpdatedAt($val)
    {
        $this->last_updated_at = $val;
    }

    public function getPublishedAt()
    {
        return $this->published_at;
    }
    public function setPublishedAt($val)
    {
        $this->published_at = $val;
    }

    public function getAdditionalId()
    {
        return $this->additional_id;
    }
    public function setAdditionalId($val)
    {
        $this->additional_id = $val;
    }

    public function getTicketId()
    {
        return $this->ticket_id;
    }
    public function setTicketId($val)
    {
        $this->ticket_id = $val;
    }

    public function getEmailChannelId()
    {
        return $this->email_channel_id;
    }
    public function setEmailChannelId($val)
    {
        $this->email_channel_id = $val;
    }

    public function getSubject()
    {
        return $this->subject;
    }
    public function setSubject($val)
    {
        $this->subject = $val;
    }
    public function geChannel()
    {
        return $this->channel;
    }
    public function setChannel($val)
    {
        $this->channel = $val;
    }
}