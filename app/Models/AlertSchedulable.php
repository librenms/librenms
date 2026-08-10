<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertSchedulable extends Model
{
    protected $table = 'alert_schedulables';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AlertSchedule::class, 'schedule_id');
    }
}
