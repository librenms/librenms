<?php
namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Log\Syslog
 *
 * @property integer $device_id
 * @property string $facility
 * @property string $priority
 * @property string $level
 * @property string $tag
 * @property string $timestamp
 * @property string $program
 * @property string $msg
 * @property integer $seq
 * @property-read \App\Models\Device $device
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereDeviceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereFacility($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog wherePriority($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereLevel($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereTag($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereTimestamp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereProgram($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereMsg($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Log\Syslog whereSeq($value)
 * @mixin \Eloquent
 */
class Syslog extends Model
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
    protected $table = 'syslog';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'seq';
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
