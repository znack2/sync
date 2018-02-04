<?php
use Usedesk\SyncEngineIntegration\Models;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @property int $id
 * @property string $sync_engine_id
 */
class SyncEngineChannel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'sync_engine_channel';
    public $timestamps  = false;
    protected $fillable = ['sync_engine_id', 'company_id', 'channel_id'];
}