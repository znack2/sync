<?php

namespace Usedesk\Sync\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


use Usedesk\Sync\Repository\SyncRepository;
use Usedesk\Sync\helpers\SyncEngineHelper;
use Usedesk\Sync\Services\SyncEngineEmail;

class SyncController
{
    private $helper;
    private $repository;

    public function __construct()
    {
        $this->helper =  new SyncEngineHelper;
        $this->repository = app(SyncRepository::class);
    }

    /**
     */
    public function createChannel(ChannelRequest $request): JsonResponse
    {
        $input = $request->all();

        $this->helper->createAccount($input['email'],$input['password']);

        $response['message'] = 'success';// ->header('Content-Type', 'application/json');

        return $this->sendResponse($response);
    }

    /**
     */
    public function syncEngine(Request $request): JsonResponse
    {
        $input = $request->all();

        foreach($input as $item){
            $object = $item['object'] ?? null;
            $event = $item['event'] ?? null;

            if ($object != 'message' or $event != 'create') {
                continue;
            }

            $this->repository->createTicketFromSync($item['attributes']);
        }

        $response['message']= 'connected successfully.';

        return $this->sendResponse($response);
    }

    /**
     */
    public function accounts(Request $request)
    {
        $result = $this->repository->getAccounts();

        return new AccountCollection($result);
    }
}
