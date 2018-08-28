<?php

namespace Usedesk\SyncIntegration\Resources;

use App\Http\Resources\VersionResource;
use App\Helpers\Project\TimeHelper;

class AccountResource extends VersionResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     */
    public function toArray($request): array
    {
        $this->setupRequest($request);

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'title'         => $this->title,
            'text'          => $this->text,
            'url'           => $this->url,
            'order_column'  => $this->order_column,
            'secret_key'    => $this->secret_key,
            'secret_key_name'    => $this->secret_key_name,
            'auth_type'    => $this->auth_type,
            'auth_type'    => $this->auth_type,

            'is_active'     => (bool) $this->is_active,
            'is_static'     => (bool) $this->is_static,

            $this->mergeWhen($this->isAdmin(), []),

            $this->mergeWhen($this->isFull($request), [
                'created_at'    => TimeHelper::convert($this->created_at),
                'updated_at'    => TimeHelper::convert($this->updated_at),
                'deleted_at'    => TimeHelper::convert($this->deleted_at),
            ]),
        ];
    }
}