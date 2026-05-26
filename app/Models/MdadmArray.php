<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $device_id
 * @property int $app_id
 * @property int|null $snmp_index
 * @property string $uuid
 * @property string|null $array_name
 * @property string|null $md_id
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
 * @property int|null $layout
 * @property int|null $resync_start_sectors
 * @property int|null $reshape_position_sectors
 * @property string|null $bitmap_type
 * @property string|null $bitmap_location
 * @property int|null $bitmap_chunksize
 * @property string|null $bitmap_metadata
 * @property int|null $bitmap_time_base
 * @property bool|null $is_mounted
 * @property string|null $mount_points
 * @property bool|null $is_swap
 * @property int|null $bitmap_backlog
 * @property int|null $bitmap_max_backlog
 * @property bool|null $bitmap_can_clear
 * @property int|null $stripe_cache_size
 * @property int|null $stripe_cache_active
 * @property string|null $journal_mode
 * @property int|null $sync_min_sectors
 * @property int|null $sync_max_sectors
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
        'snmp_index',
        'uuid',
        'array_name',
        'md_id',
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
        'layout',
        'resync_start_sectors',
        'reshape_position_sectors',
        'bitmap_type',
        'bitmap_location',
        'bitmap_chunksize',
        'bitmap_metadata',
        'bitmap_time_base',
        'is_mounted',
        'mount_points',
        'is_swap',
        'bitmap_backlog',
        'bitmap_max_backlog',
        'bitmap_can_clear',
        'stripe_cache_size',
        'stripe_cache_active',
        'journal_mode',
        'sync_min_sectors',
        'sync_max_sectors',
    ];

    protected $casts = [
        'is_mounted' => 'boolean',
        'is_swap' => 'boolean',
        'bitmap_can_clear' => 'boolean',
    ];

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'app_id', 'app_id');
    }

    /**
     * @return HasMany<MdadmDrive, $this>
     */
    public function drives(): HasMany
    {
        return $this->hasMany(MdadmDrive::class, 'mdadm_array_id');
    }
}
