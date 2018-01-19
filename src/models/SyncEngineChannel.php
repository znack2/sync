<?php
namespace UseDesk\SyncEngineIntegration\models;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @property int $id
 * @property string $sync_engine_id
 */
class SyncEngineChannel extends BaseModel
{
    protected $table = 'company_email_channels';

    protected $fillable = ['sync_engine_id'];

}