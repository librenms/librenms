<?php

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Vlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Device>
 */
class VlanDevicesController extends TableController
{
    protected function sortFields(Request $request): array
    {
        return [
            'device' => 'device_id',
            'ports_count',
            'domain' => 'vlans.vlan_domain',
            'name' => 'vlans.vlan_name',
            'type' => 'vlans.vlan_type',
            'state' => 'vlans.vlan_state',
        ];
    }

    private int $vlanId;

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Vlan::class);

        $this->validate($request, ['vlan' => 'integer']);
        $this->vlanId = $request->input('vlan', 1);

        return Device::distinct()
            ->hasAccess($request->user())
            ->with('location')
            ->select([
                'devices.*',
                'vlans.vlan_name',
                'vlans.vlan_type',
                'vlans.vlan_state',
            ])
            ->withCount(['ports' => function ($query): void {
                $query->distinct()->where('ifVlan', $this->vlanId)
                    ->orWhereHas('vlans', fn ($q) => $q->where('vlan', $this->vlanId));
            }])
            ->where(function ($query): void {
                $query->where('vlans.vlan_vlan', $this->vlanId)
                    ->orWhereHas('ports', fn ($q) => $q->where('ifVlan', $this->vlanId));
            })
        ->leftJoin('vlans', function ($join): void {
            $join->on('devices.device_id', '=', 'vlans.device_id')
            ->on('vlans.vlan_vlan', '=', DB::raw($this->vlanId));
        });
    }

    /**
     * @param  Device  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'device' => Url::deviceLink($model),
            'ports_count' => $model->ports_count,
            // left joined fields
            'domain' => $model['vlan_domain'],
            'name' => $model['vlan_name'],
            'type' => $model['vlan_type'],
            'state' => $model['vlan_state'],
        ];
    }
}
