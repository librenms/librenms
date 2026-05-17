<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $device_id
 * @property int $app_id
 * @property string $uuid
 * @property string|null $array_name
 * @property string|null $name
 * @property string|null $level
 * @property string|null $state
 * @property int|null $size_bytes
 * @property int|null $raid_disks
 * @property string|null $metadata_version
 * @property string|null $consistency_policy
 * @property int|null $chunk_size
 * @property int|null $active_devices
 * @property int|null $working_devices
 * @property int|null $spare_devices
 * @property int|null $failed_devices
 * @property int|null $degraded
 * @property int|null $mismatch_cnt
 * @property string|null $sync_action
 * @property float|null $sync_completed_pct
 * @property int|null $sync_speed_bps
 * @property int|null $sync_speed_min_bps
 * @property int|null $sync_speed_max_bps
 * @property int|null $sync_done_bytes
 * @property int|null $sync_total_bytes
 * @property string|null $sync_last_action
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Device $device
 * @property-read Application $application
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MdadmDrive> $drives
 */
class MdadmArray extends Model
{
    protected $table = 'mdadm_arrays';

    protected $fillable = [
        'device_id',
        'app_id',
        'uuid',
        'array_name',
        'name',
        'level',
        'state',
        'size_bytes',
        'raid_disks',
        'metadata_version',
        'consistency_policy',
        'chunk_size',
        'active_devices',
        'working_devices',
        'spare_devices',
        'failed_devices',
        'degraded',
        'mismatch_cnt',
        'sync_action',
        'sync_completed_pct',
        'sync_speed_bps',
        'sync_speed_min_bps',
        'sync_speed_max_bps',
        'sync_done_bytes',
        'sync_total_bytes',
        'sync_last_action',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'app_id', 'app_id');
    }

    public function drives(): HasMany
    {
        return $this->hasMany(MdadmDrive::class, 'mdadm_array_id');
    }
}
