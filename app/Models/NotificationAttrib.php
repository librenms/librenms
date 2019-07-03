<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAttrib extends Model
{
    public $timestamps = false;
    protected $table = 'notifications_attribs';
    protected $primaryKey = 'attrib_id';
    protected $fillable = ['notifications_id', 'user_id', 'key', 'value'];

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notification()
    {
        return $this->belongsTo('App\Models\Notification', 'notifications_id');
    }
}
