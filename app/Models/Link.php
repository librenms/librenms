<?php

namespace App\Models;

use App\Facades\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Gate;

class Link extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function scopeHasAccess(Builder $query, User $user): Builder
    {
        if (
            Gate::forUser($user)->allows('viewAll', Link::class)
            || Gate::forUser($user)->allows('viewAll', Device::class)
            || Gate::forUser($user)->allows('viewAll', Port::class)
        ) {
            return $query;
        }

        return $query->where(function ($query) use ($user) {
            return $query->whereIntegerInRaw('links.local_port_id', Permissions::portsForUser($user))
                ->orWhereIntegerInRaw("links.local_device_id", Permissions::devicesForUser($user));
        })->where(function (Builder $query) use ($user) {
            return $query->where('links.remote_device_id', 0)
                ->orWhereIntegerInRaw('links.remote_port_id', Permissions::portsForUser($user))
                ->orWhereIntegerInRaw('links.remote_device_id', Permissions::devicesForUser($user));
        });
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'local_device_id', 'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'local_port_id', 'port_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Device, $this>
     */
    public function remoteDevice(): HasOne
    {
        return $this->hasOne(Device::class, 'device_id', 'remote_device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Port, $this>
     */
    public function remotePort(): HasOne
    {
        return $this->hasOne(Port::class, 'port_id', 'remote_port_id');
    }
}
