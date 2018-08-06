<?php

namespace Usedesk\Sync\Services;

use Carbon\Carbon;
use Exception;

class SyncEngineComment
{
    protected $message = null;
    protected $type = null;
    protected $from = '';
    protected $client_id = null;
    protected $trigger_id = null;
    protected $user_id = null;
    protected $published_at = null;
    protected $ticket_id = null;
    protected $comment_id = null;
    protected $to = null;
    protected $cc = null;
    protected $bcc = null;
    protected $files = null;
    public function __construct($params)
    {
        if(is_array($params)){
            $this->fillFromArray($params);
        }
        else{
            $this->fill($params);
        }
        $date = Carbon::now();
        $this->setPublishedAt($date);
    }

    protected function fillFromArray($data)
    {
        if(is_null($data)) return;
        isset($data['body']) && $this->setMessage($data['body']);
        isset($data['from']) && $this->setFrom($data['from']);
        isset($data['client_id']) && $this->setClientId($data['client_id']);
        isset($data['trigger_id']) && $this->setTriggerId($data['trigger_id']);
        isset($data['user_id']) && $this->setUserId($data['user_id']);
        isset($data['published_at']) && $this->setPublishedAt($data['published_at']);
        isset($data['ticket_id']) && $this->setTicketId($data['ticket_id']);
        isset($data['type']) && $this->setType($data['type']);
        isset($data['comment_id']) && $this->setCommentId($data['comment_id']);
        isset($data['to']) && $this->setTo($data['to']);
        isset($data['cc']) && $this->setCc($data['cc']);
        isset($data['bcc']) && $this->setBcc($data['bcc']);
        isset($data['files']) && $this->setFiles($data['files']);
    }
    protected function fill($data)
    {
        if(is_null($data)) return;
        isset($data->body) && $this->setMessage($data->body);
        isset($data->from) && $this->setFrom($data->from);
        isset($data->client_id) && $this->setClientId($data->client_id);
        isset($data->trigger_id) && $this->setTriggerId($data->trigger_id);
        isset($data->user_id) && $this->setUserId($data->user_id);
        isset($data->published_at) && $this->setPublishedAt($data->published_at);
        isset($data->ticket_id) && $this->setTicketId($data->ticket_id);
        isset($data->type) && $this->setType($data->type);
        isset($data->comment_id) && $this->setCommentId($data->comment_id);
        isset($data->to) && $this->setTo($data->to);
        isset($data->cc) && $this->setCc($data->cc);
        isset($data->bcc) && $this->setBcc($data->bcc);
        isset($data->files) && $this->setFiles($data->files);
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function setMessage($val)
    {
        $this->message = $val;
    }

    public function getFrom()
    {
        return $this->from;
    }
    public function setFrom($val)
    {
        $this->from = $val;
    }

    public function getType()
    {
        return $this->type;
    }
    public function setType($val)
    {
        $this->type = $val;
    }

    public function getClientId()
    {
        return $this->client_id;
    }
    public function setClientId($val)
    {
        $this->client_id = $val;
    }


    public function getTriggerId()
    {
        return $this->trigger_id;
    }
    public function setTriggerId($val)
    {
        $this->trigger_id = $val;
    }

    public function getUserId()
    {
        return $this->user_id;
    }
    public function setUserId($val)
    {
        $this->user_id = $val;
    }


    public function getPublishedAt()
    {
        return $this->published_at;
    }
    public function setPublishedAt($val)
    {
        $this->published_at = $val;
    }


    public function getTicketId()
    {
        return $this->ticket_id;
    }
    public function setTicketId($val)
    {
        $this->ticket_id = $val;
    }
    public function getCommentId()
    {
        return $this->comment_id;
    }
    public function setCommentId($val)
    {
        $this->comment_id = $val;
    }

    public function getTo()
    {
        return $this->to;
    }
    public function setTo($val)
    {
        $this->to = $val;
    }

    public function getBcc()
    {
        return $this->bcc;
    }
    public function setBcc($val)
    {
        $this->bcc = $val;
    }

    public function getCc()
    {
        return $this->cc;
    }
    public function setCc($val)
    {
        $this->cc = $val;
    }
    public function getFiles()
    {
        return $this->files;
    }
    public function setFiles($val)
    {
        $this->files = $val;
    }
}