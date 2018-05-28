<?php
namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

class Alertlog extends Model
{
    const UPDATED_AT = 'time_logged';

    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 'alert_log';
    /**
     * The primary key column name.
     *
     * @var string
     */

    // ---- Accessors/Mutators ----

    public function setCreatedAt($value)
    {
        // Created at fields don't exist
    }

    public function getCreatedAt($value)
    {
        // Created at fields don't exist
    }

    // ---- Define Relationships ----
    /**
     * Returns the device this entry belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
