<?php

namespace usedesk\SyncEngineIntegration\Jobs;

interface CreateTicketInterface {

    public function __construct(array $data, array $attachments);

}