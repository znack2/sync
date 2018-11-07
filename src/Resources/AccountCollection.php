<?php

namespace Freshplan\Sync\Resources;

use App\Http\Resources\Base\BaseResourceCollection;
use Freshplan\Sync\\Resources\AccountResource;

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
