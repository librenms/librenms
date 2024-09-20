<?php

namespace App\Http\Controllers\Table;

use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Url;

class VlanPortsController extends TableController
{
    protected function sortFields($request)
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

    protected function baseQuery(Request $request)
    {
        $this->validate($request, ['vlan' => 'integer']);
        $this->vlanId = $request->get('vlan', 1);

        return Port::with('device')
            ->leftJoin('ports_vlans', 'ports.port_id', 'ports_vlans.port_id')
            ->where(function ($query) {
                $query->where('ifVlan', $this->vlanId)
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
     * @param  Port  $port
     * @return array
     */
    public function formatItem($port)
    {
//        dd($port->toArray());
        return [
            'device' => Url::deviceLink($port->device),
            'port' => Url::portLink($port),
            // left joined columns
            'untagged' => $port->untagged,
            'state' => $port->state,
            'cost' => $port->cost,
        ];
    }
}
