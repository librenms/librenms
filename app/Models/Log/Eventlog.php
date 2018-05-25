<?php
namespace App\Models\Log;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\Log\Eventlog
 *
 * @property integer $event_id
 * @property integer $host
 * @property string $datetime
 * @property integer $device_id
 * @property string $message
 * @property string $type
 * @property string $reference
 * @property-read \App\Models\Device $device
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereEventId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereHost($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereDatetime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereDeviceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereMessage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Eventlog whereReference($value)
 * @mixin \Eloquent
 */
class Eventlog extends Model
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
    protected $table = 'eventlog';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'event_id';
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