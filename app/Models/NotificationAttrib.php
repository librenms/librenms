<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAttrib extends Model
{

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications_attribs';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'attrib_id';

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
