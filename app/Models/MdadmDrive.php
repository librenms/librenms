<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $device_id
 * @property int $app_id
 * @property int $mdadm_array_id
 * @property string $dev_id
 * @property string|null $path
 * @property string|null $state
 * @property array|null $state_flags
 * @property int|null $errors
 * @property bool $is_missing
 * @property int|null $size_bytes
 * @property string|null $device_role
 * @property int|null $slot
 * @property string|null $id_model
 * @property string|null $id_serial_short
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
        'dev_id',
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
    ];

    protected $casts = [
        'state_flags' => 'array',
        'is_missing'  => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function mdadmArray(): BelongsTo
    {
        return $this->belongsTo(MdadmArray::class, 'mdadm_array_id');
    }
}
