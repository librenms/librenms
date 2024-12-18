<?php

namespace App\Http\Controllers\Table;

use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Url;

class VlanPortsController extends TableController
{
    protected function searchFields(Request $request): array
    {
        return [
            'ifName',
            'ifDescr',
            'ifAlias',
        ];
    }

    protected function sortFields($request): array
    {
        return [
            'device' => 'device_id',
            'port' => 'port_id',
            'untagged',
            'state' => 'ports_vlans.state',
            'cost' => 'ports_vlans.cost',
        ];
    }

    private int $vlanId;

    protected function baseQuery(Request $request): Builder
    {
        $this->validate($request, ['vlan' => 'integer']);
        $this->vlanId = $request->get('vlan', 1);

        return Port::with(['device', 'device.location'])
            ->leftJoin('ports_vlans', 'ports.port_id', 'ports_vlans.port_id')
            ->where(function ($query) {
                $query->where(fn ($q) => $q->where('ifVlan', $this->vlanId)->whereNull('ports_vlans.vlan'))
                    ->orWhere('ports_vlans.vlan', $this->vlanId);
            })
            ->select([
                'ports.port_id',
                'ports.device_id',
                'ports.ifName',
                'ports.ifIndex',
                'ports.ifDescr',
                'ports.ifAlias',
                'ports.ifVlan',
                'ports.ifAdminStatus',
                'ports.ifOperStatus',
                'ports_vlans.untagged',
                'ports_vlans.state',
                'ports_vlans.cost',
                DB::raw("CASE WHEN ports.ifVlan = $this->vlanId or ports_vlans.untagged <> 0 THEN \"yes\" ELSE \"no\" END as untagged"),
            ]);
    }

    /**
     * @param  Port  $model
     */
    public function formatItem($model): array
    {
        return [
            'device' => Url::deviceLink($model->device),
            'port' => Url::portLink($model, $model->getFullLabel()),
            // left joined columns
            'untagged' => $model['untagged'],
            'state' => $model['state'],
            'cost' => $model['cost'],
        ];
    }
}
