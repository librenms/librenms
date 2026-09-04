<?php

namespace App\Actions\Port;

use App\Models\Device;
use App\Models\PortGroup;
use Illuminate\Support\Facades\DB;
use Log;

class UpdatePortGroupsAction
{
    public function __construct(private readonly Device $device)
    {
    }

    /**
     * Re-evaluate the dynamic port groups for the ports of this device.
     *
     * Everything is scoped to this device: the matching query is bounded by
     * ports.device_id and only the pivot rows for this device's ports are read.
     * That keeps the cost proportional to the number of ports on the device and
     * the number of dynamic groups, not to the size of the groups or the estate.
     * A plain sync() on the relation would instead drop the ports contributed by
     * every other device.
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

        // read the pivot for every group in one covering-index query rather than
        // once per group, and keep it bounded to this device's ports
        $current_by_group = DB::table('port_group_port')
            ->whereIntegerInRaw('port_id', $device_port_ids)
            ->get(['port_group_id', 'port_id'])
            ->groupBy('port_group_id')
            ->map(fn ($rows) => $rows->pluck('port_id'));

        foreach (PortGroup::where('type', 'dynamic')->get() as $port_group) {
            try {
                $matching = $port_group->getPortIdQuery()
                    ?->where('ports.device_id', $this->device->device_id)
                    ->pluck('ports.port_id') ?? collect();
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error("Port Group '$port_group->name' generates invalid query: " . $e->getMessage());

                continue;
            }

            $current = $current_by_group->get($port_group->id, collect());

            $detached += $port_group->detachPorts($current->diff($matching));
            $attached += $port_group->attachPorts($matching->diff($current));
        }

        return ['attached' => $attached, 'detached' => $detached];
    }
}
