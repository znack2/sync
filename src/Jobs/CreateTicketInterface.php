<?php

namespace Usedesk\SyncEngineIntegration\Jobs;

class CreateTicket
{

    public $attachments;
    public $data;

    public function __construct(array $data, array $attachments = [])
    {
        $this->attachments = $this->uploadAttachments($attachments);
    }

    public function handle()
    {

    }

    protected function push($attachments): array
    {

    }

    protected function uploadAttachments(array $attachments) : array
    {
        foreach ($attachments as $file) {
            if (isset($file['id'])) {
                $file_id = $file['id'];
                $url = 'http://' . $account_id . '@188.93.209.204:15555/files/' . $file_id . '/download';
            }
        }
    }
}