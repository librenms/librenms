<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevicePerm extends DeviceRelatedModel
{
    protected $table = 'devices_perms';
    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
