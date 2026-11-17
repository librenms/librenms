<?php

/**
 * Pseudowire.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @phpstan-type CircuitEndpoint array{device: ?Device, description: ?string, port: ?Port, mtu: ?int} */
class Pseudowire extends PortRelatedModel
{
    /** @use HasFactory<\Database\Factories\PseudowireFactory> */
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'pseudowire_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function peerDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'peer_device_id', 'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pseudowire, $this>
     */
    public function endpoints(): HasMany
    {
        return $this->hasMany(Pseudowire::class, 'cpwVcId', 'cpwVcId');
    }

    /**
     * List each accessible circuit once, scoped to a local device when given.
     *
     * @return list<array{
     *     vcId: int,
     *     type: string,
     *     psnType: string,
     *     endpoints: array{local: CircuitEndpoint, remote: CircuitEndpoint},
     * }>
     */
    public static function circuits(User $user, ?Device $device = null): array
    {
        $query = self::hasAccess($user)
            ->with(['port.device', 'peerDevice'])
            ->join('ports', 'pseudowires.port_id', '=', 'ports.port_id');

        if ($device) {
            $query->whereBelongsTo($device, 'device');
        } else {
            $query->with('device')
                ->join('devices', 'pseudowires.device_id', '=', 'devices.device_id')
                ->orderBy('devices.hostname');
        }

        $pseudowires = $query->orderBy('ports.ifDescr')->select('pseudowires.*')->get();
        $peerDeviceIds = $pseudowires->pluck('peer_device_id')->filter()->unique();
        $peers = $peerDeviceIds->isEmpty()
            ? collect()
            : self::hasAccess($user)
                ->with(['port.device', 'device'])
                ->whereIntegerInRaw('device_id', $peerDeviceIds)
                ->whereIntegerInRaw('cpwVcID', $pseudowires->pluck('cpwVcID')->unique())
                ->get()
                ->keyBy(fn (self $peer) => "$peer->device_id:$peer->peer_device_id:$peer->cpwVcID");

        $circuits = [];
        $paired = [];
        foreach ($pseudowires as $pseudowire) {
            if (isset($paired[$pseudowire->pseudowire_id])) {
                continue;
            }

            // The remote device may have discovered the circuit before it could resolve the local device.
            $peer = $peers->get("$pseudowire->peer_device_id:$pseudowire->device_id:$pseudowire->cpwVcID")
                ?? $peers->get("$pseudowire->peer_device_id:0:$pseudowire->cpwVcID");

            if ($peer && ($peer->is($pseudowire) || isset($paired[$peer->pseudowire_id]))) {
                $peer = null;
            }

            if ($peer) {
                $paired[$peer->pseudowire_id] = true;
                unset($circuits[$peer->pseudowire_id]);
            }

            $circuits[$pseudowire->pseudowire_id] = [
                'vcId' => $pseudowire->cpwVcID,
                'type' => $pseudowire->pw_type,
                'psnType' => $pseudowire->pw_psntype,
                'endpoints' => [
                    'local' => [
                        'device' => $device ?? $pseudowire->device,
                        'description' => $pseudowire->pw_descr,
                        'port' => $pseudowire->port,
                        'mtu' => $pseudowire->pw_local_mtu,
                    ],
                    'remote' => [
                        'device' => $peer->device ?? $pseudowire->peerDevice,
                        'description' => $peer?->pw_descr,
                        'port' => $peer?->port,
                        'mtu' => $peer?->pw_local_mtu,
                    ],
                ],
            ];
        }

        return array_values($circuits);
    }
}
