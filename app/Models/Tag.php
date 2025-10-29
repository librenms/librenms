<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LibreNMS\Interfaces\Models\Keyable;

class Tag extends Model implements Keyable
{
    protected $table = 'tags';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;

    protected $fillable = [
        'object_type',
        'object_id',
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
        return $this->hasOne(Device::class, 'device_id', 'object_id');
    }

    /**
     * Tag key relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\TagKey, $this>
     */
    public function tagKey(): BelongsTo
    {
        return $this->belongsTo(TagKey::class, 'tag_key_id');
    }

    /**
     * Get a string that can identify a unique instance of this model
     *
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->object_type . ':' . $this->object_id . ':' . $this->tag_key_id;
    }

    /**
     * Set the value attribute, validating against the tag key type.
     *
     * @param  mixed  $value
     *
     * @throws \InvalidArgumentException
     */
    public function setValueAttribute($value)
    {
        $tagKey = $this->tagKey;
        if (! $tagKey->validateValue($value)) {
            throw new \InvalidArgumentException("Value for tag '{$this->tagKey?->key}' does not match type '{$this->tagKey?->type}'");
        }
        $this->attributes['value'] = $value;
    }
}
