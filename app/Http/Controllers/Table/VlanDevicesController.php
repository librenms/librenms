<?php

namespace App\Http\Controllers\Table;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Url;

class VlanDevicesController extends TableController
{
    protected function sortFields($request)
    {
        return [
            'device' => 'device_id',
            'ports_count',
            'domain' => 'vlans.vlan_domain',
            'name' => 'vlans.vlan_name',
            'type' => 'vlans.vlan_type',
            'mtu' => 'vlans.vlan_mtu',
        ];
    }

    private int $vlanId;

    protected function baseQuery(Request $request)
    {
        $this->validate($request, ['vlan' => 'integer']);
        $this->vlanId = $request->get('vlan', 1);

        return Device::distinct()
            ->with('location')
            ->select([
                'devices.*',
                'vlans.vlan_name',
                'vlans.vlan_type',
                'vlans.vlan_mtu',
            ])
            ->withCount(['ports' => function ($query) {
                $query->distinct()->where('ifVlan', $this->vlanId)
                    ->orWhereHas('vlans', fn ($q) => $q->where('vlan', $this->vlanId));
            }])
            ->where(function ($query) {
                $query->where('vlans.vlan_vlan', $this->vlanId)
                    ->orWhereHas('ports', fn ($q) => $q->where('ifVlan', $this->vlanId));
            })
        ->leftJoin('vlans', function ($join) {
            $join->on('devices.device_id', '=', 'vlans.device_id')
            ->on('vlans.vlan_vlan', '=', DB::raw($this->vlanId));
        });
    }

    /**
     * @param  Device  $model
     */
    public function formatItem($model): array
    {
        return [
            'device' => Url::deviceLink($model),
            'ports_count' => $model->ports_count,
            // left joined fields
            'domain' => $model['vlan_domain'],
            'name' => $model['vlan_name'],
            'type' => $model['vlan_type'],
            'mtu' => $model['vlan_mtu'],
        ];
    }
}
