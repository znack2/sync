<?php

namespace Usedesk\SyncEngineIntegration\Jobs;

interface CreateTicketInterface {

    public function __construct(array $data, array $attachments);

}