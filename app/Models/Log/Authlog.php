<?php
namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

class Authlog extends Model
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
    
    protected $table = 'authlog';
    /**
     * The primary key column name.
     *
     * @var string
     */

    // ---- Accessors/Mutators ----
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