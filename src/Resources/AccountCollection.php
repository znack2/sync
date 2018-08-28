<?php

namespace Usedesk\SyncIntegration\Resources;

use App\Http\Resources\Base\BaseResourceCollection;
use Usedesk\SyncIntegration\Resources\AccountResource;

class AccountCollection extends BaseResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return AccountResource::collection($this->collection);
    }
}
