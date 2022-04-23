<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAttrib extends Model
{
    public $timestamps = false;
    protected $table = 'notifications_attribs';
    protected $primaryKey = 'attrib_id';
    protected $fillable = ['notifications_id', 'user_id', 'key', 'value'];

    // ---- Define Relationships ----

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Notification::class, 'notifications_id');
    }
}
