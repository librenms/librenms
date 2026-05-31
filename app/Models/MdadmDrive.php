<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $device_id
 * @property int $app_id
 * @property int $mdadm_array_id
 * @property int|null $snmp_index
 * @property string $dev_id
 * @property string|null $dev_uuid
 * @property string|null $path
 * @property string|null $state
 * @property list<string>|null $state_flags
 * @property int|null $errors
 * @property bool $is_missing
 * @property int|null $size_bytes
 * @property string|null $device_role
 * @property int|null $slot
 * @property string|null $id_model
 * @property string|null $id_serial_short
 * @property int|null $offset_sectors
 * @property int|null $ppl_sector
 * @property int|null $ppl_size_sectors
 * @property int|null $events
 * @property int|null $recovery_start_sectors
 * @property int|null $bad_block_count
 * @property int|null $unack_bad_block_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Device $device
 * @property-read MdadmArray $mdadmArray
 */
class MdadmDrive extends Model
{
    protected $table = 'mdadm_drives';

    protected $fillable = [
        'device_id',
        'app_id',
        'mdadm_array_id',
        'snmp_index',
        'dev_id',
        'dev_uuid',
        'path',
        'state',
        'state_flags',
        'errors',
        'is_missing',
        'size_bytes',
        'device_role',
        'slot',
        'id_model',
        'id_serial_short',
        'offset_sectors',
        'ppl_sector',
        'ppl_size_sectors',
        'events',
        'recovery_start_sectors',
        'bad_block_count',
        'unack_bad_block_count',
    ];

    protected $casts = [
        'state_flags' => 'array',
        'is_missing' => 'boolean',
    ];

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    /**
     * @return BelongsTo<MdadmArray, $this>
     */
    public function mdadmArray(): BelongsTo
    {
        return $this->belongsTo(MdadmArray::class, 'mdadm_array_id');
    }
}
