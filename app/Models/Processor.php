<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Processor
 *
 * @property integer $processor_id
 * @property integer $device_id
 * @property-read \App\Models\Device $device
 * @mixin \Eloquent
 * @property integer $entPhysicalIndex
 * @property integer $hrDeviceIndex
 * @property string $processor_oid
 * @property string $processor_index
 * @property string $processor_type
 * @property integer $processor_usage
 * @property string $processor_descr
 * @property integer $processor_precision
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereEntPhysicalIndex($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereHrDeviceIndex($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereDeviceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorOid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorIndex($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorUsage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorDescr($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Processor whereProcessorPrecision($value)
 */
class Processor extends Model
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
    protected $table = 'processors';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'processor_id';

    // ---- Helper Functions ----

    /**
     * Return Processor Description, formatted for display
     *
     * @return string
     */
    public function getFormattedDescription()
    {
        $bad_descr = array(
            'GenuineIntel:',
            'AuthenticAMD:',
            'Intel(R)',
            'CPU',
            '(R)',
            '(tm)',
        );

        $descr = str_replace($bad_descr, '', $this->processor_descr);

        // reduce extra spaces
        $descr = str_replace('  ', ' ', $descr);

        return $descr;
    }

    // ---- Query scopes ----


    // ---- Accessors/Mutators ----


    // ---- Define Relationships ----

    /**
     * Get the device this port belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
