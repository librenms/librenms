<?php

namespace App\Actions\Port;

use App\Models\Device;
use App\Models\PortGroup;
use Log;

class UpdatePortGroupsAction
{
    public function __construct(private readonly Device $device)
    {
    }

    /**
     * Re-evaluate the dynamic port groups for the ports of this device.
     *
     * Membership is synced per device rather than per group: a full sync() on the
     * relation would drop the ports contributed by every other device. This costs
     * one query per dynamic group, not one per port.
     *
     * @return array{attached: int, detached: int}
     */
    public function execute(): array
    {
        if (! $this->device->exists) {
            return ['attached' => 0, 'detached' => 0];
        }

        $device_port_ids = $this->device->ports()->pluck('port_id');
        $attached = 0;
        $detached = 0;

        foreach (PortGroup::where('type', 'dynamic')->get() as $port_group) {
            try {
                $matching = $port_group->getPortIdQuery()
                    ?->where('ports.device_id', $this->device->device_id)
                    ->pluck('ports.port_id') ?? collect();
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error("Port Group '$port_group->name' generates invalid query: " . $e->getMessage());

                continue;
            }

            $remove = $device_port_ids->diff($matching)->all();
            if ($remove) {
                $detached += $port_group->ports()->detach($remove);
            }

            $changes = $port_group->ports()->syncWithoutDetaching($matching->all());
            $attached += count($changes['attached']);
        }

        return ['attached' => $attached, 'detached' => $detached];
    }
}
