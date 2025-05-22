<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LibreNMS\Interfaces\Models\Keyable;

class DeviceTag extends Model implements Keyable
{
    protected $table = 'tags';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'tag_key_id',
        'value',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Device, $this>
     */
    public function parentDevice(): HasOne
    {
        return $this->hasOne(Device::class, 'device_id', 'device_id');
    }

    /**
     * Tag key relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\DeviceTagKey, $this>
     */
    public function tagKey(): BelongsTo
    {
        return $this->belongsTo(DeviceTagKey::class, 'tag_key_id');
    }

    /**
     * Get a string that can identify a unique instance of this model
     *
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->device_id . ':' . $this->tag_key_id;
    }
}
