<?php

/**
 * PortController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Facades\Rrd;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\JuniAtmVp;
use App\Models\MacAccounting;
use App\Models\Port;
use App\Models\PortVlan;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Interfaces\Plugins\Hooks\PortTabHook;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Plugins;

class PortController extends Controller implements DeviceTab
{
    /** graph type => [title, rrd suffix that must exist] */
    private const array GRAPHS = [
        'port_bits' => ['Traffic', ''],
        'port_upkts' => ['Packets (Unicast)', ''],
        'port_nupkts' => ['Packets (Non Unicast)', ''],
        'port_errors' => ['Errors', ''],
        'port_poe' => ['PoE', 'poe'],
        'port_etherlike' => ['Ethernet Errors', 'dot3'],
        'port_cie' => ['Detailed Interface Errors', 'cie'],
    ];

    private const array XDSL_GRAPHS = [
        'port_adsl_speed' => ['ADSL Current Line Speed', 'adsl'],
        'port_adsl_attainable' => ['ADSL Attainable Speed', 'adsl'],
        'port_adsl_attenuation' => ['ADSL Line Attenuation', 'adsl'],
        'port_adsl_snr' => ['ADSL Line SNR Margin', 'adsl'],
        'port_adsl_power' => ['ADSL Output Powers', 'adsl'],
        'port_vdsl_speed' => ['VDSL Current Line Speed', 'xdsl2LineStatusAttainableRate'],
        'port_vdsl_attainable' => ['VDSL Attainable Speed', 'xdsl2LineStatusAttainableRate'],
        'port_vdsl_power' => ['VDSL Output Powers', 'xdsl2LineStatusAttainableRate'],
    ];

    private const array ATM_GRAPHS = ['bits' => 'Bits', 'packets' => 'Packets', 'cells' => 'Cells', 'errors' => 'Errors'];

    private const array REALTIME_INTERVALS = ['0.25', '1', '2', '5', '15', '60'];

    public function visible(Device $device): bool
    {
        return false;
    }

    public function slug(): string
    {
        return 'port';
    }

    public function icon(): string
    {
        return 'fa-link';
    }

    public function name(): string
    {
        return __('Port');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function show(Device $device, Port $port, Request $request): View
    {
        $this->authorize('view', $port);

        $request->validate([
            'view' => 'nullable|string',
            'show' => 'nullable|integer',
            'graph' => 'nullable|string',
            'subview' => 'nullable|string',
            'interval' => 'nullable|string',
            'sort' => 'nullable|string',
            'period' => 'nullable|string',
        ]);

        $port->setRelation('device', $device);

        $views = $this->views($device, $port);
        $view = $request->string('view', 'graphs')->value();
        $view = isset($views[$view]) || in_array($view, ['macaccounting', 'junose-atm-vp']) ? $view : 'graphs';

        return view('device.tabs.port', [
            'device' => $device,
            'port' => $port,
            'view' => $view,
            'menus' => $this->menus($port, $views, $view, $request),
            'bill' => $this->billLink($port),
            'neighbors' => [$port->port_id => (new PortsController)->findPortNeighbors($port)],
            'portsWithEvents' => (new PortsController)->portsWithEvents($device->device_id, [$port->port_id]),
            'viewData' => match ($view) {
                'graphs' => ['graphs' => $this->existingGraphs($device, $port, self::GRAPHS)],
                'xdsl' => ['graphs' => $this->existingGraphs($device, $port, self::XDSL_GRAPHS)],
                'realtime' => $this->realtime($device, $request),
                'notes' => ['notes' => $device->getAttrib("port_id_notes:$port->port_id") ?? ''],
                'sensors' => ['sensors' => $this->sensors($device, $port)->get()],
                'pagp' => ['members' => $device->ports()->where('pagpGroupIfIndex', $port->ifIndex)->get()],
                'vlans' => ['vlans' => $this->vlans($device, $port)],
                'macaccounting' => $this->macAccounting($port, $request),
                'junose-atm-vp' => $this->atmVps($port, $request),
                'qos' => ['show' => $request->filled('show') ? $request->integer('show') : null],
                'plugins' => ['plugins' => $this->plugins($device, $port)],
                default => [],
            },
        ]);
    }

    /**
     * Port sub views available for this port
     *
     * @return array<string, string>
     */
    private function views(Device $device, Port $port): array
    {
        $views = [
            'graphs' => __('Graphs'),
            'realtime' => __('Real time'),
        ];

        if ($port->macs()->exists()) {
            $views['arp'] = __('ARP Table');
        }

        if ($port->nd()->exists()) {
            $views['nd'] = __('IPv6 ND');
        }

        if ($port->fdbEntries()->exists()) {
            $views['fdb'] = __('FDB Table');
        }

        $views['notes'] = $device->getAttrib("port_id_notes:$port->port_id") ? __('Notes') . '*' : __('Notes');

        if ($port->transceivers()->exists()) {
            $views['transceiver'] = __('port.transceiver');
        }

        if ($this->sensors($device, $port)->exists()) {
            $views['sensors'] = __('Health');
        }

        if ($port->adsl()->exists() || $port->vdsl()->exists()) {
            $views['xdsl'] = __('xDSL');
        }

        if ($port->nac()->exists()) {
            $views['nac'] = __('NAC');
        }

        if ($device->ports()->where('pagpGroupIfIndex', $port->ifIndex)->exists()) {
            $views['pagp'] = __('PAgP');
        }

        if ($port->vlans()->exists()) {
            $views['vlans'] = __('VLANs');
        }

        if ($port->qos()->exists()) {
            $views['qos'] = __('QoS');
        }

        if (Plugins::countHooks('port_container') || \PluginManager::hasHooks(PortTabHook::class, ['port' => $port])) {
            $views['plugins'] = __('Plugins');
        }

        return $views;
    }

    /**
     * Option bars for the port sub views
     *
     * @param  array<string, string>  $views
     * @return list<array{name: string, options: array<string, array{text: string, link: string}>, selected: ?string}>
     */
    private function menus(Port $port, array $views, string $view, Request $request): array
    {
        $options = collect($views)->map(fn ($text, $view) => ['text' => $text, 'link' => route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => $view])])->all();
        $options['eventlog'] = ['text' => __('Eventlog'), 'link' => route('device.eventlog', ['device' => $port->device_id, 'port' => $port->port_id])];
        $menus = [['name' => '', 'options' => $options, 'selected' => $view]];

        if ($port->macAccounting()->exists()) {
            $options = [];
            foreach (['bits' => __('Bits'), 'pkts' => __('Packets')] as $graph => $text) {
                foreach (['graphs' => $text, 'minigraphs' => "$text (" . __('Mini') . ')', 'top10' => "$text (" . __('Top 10') . ')'] as $subview => $subtext) {
                    $options["$graph-$subview"] = ['text' => $subtext, 'link' => route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'macaccounting', 'subview' => $subview, 'graph' => $graph])];
                }
            }

            $menus[] = [
                'name' => __('MAC Accounting'),
                'options' => $options,
                'selected' => $view == 'macaccounting' ? $request->string('graph', 'bits') . '-' . $request->string('subview', 'graphs') : null,
            ];
        }

        if (JuniAtmVp::where('port_id', $port->port_id)->exists()) {
            $menus[] = [
                'name' => __('ATM VPs'),
                'options' => collect(self::ATM_GRAPHS)->map(fn ($text, $graph) => ['text' => __($text), 'link' => route('device.port', ['device' => $port->device_id, 'port' => $port->port_id, 'view' => 'junose-atm-vp', 'graph' => $graph])])->all(),
                'selected' => $view == 'junose-atm-vp' ? $request->string('graph', 'bits')->value() : null,
            ];
        }

        return $menus;
    }

    /**
     * @param  array<string, array{string, string}>  $graphs
     * @return array<string, string>
     */
    private function existingGraphs(Device $device, Port $port, array $graphs): array
    {
        $exists = [];

        return collect($graphs)
            ->filter(function ($graph, $type) use ($device, $port, &$exists) {
                $suffix = $graph[1];
                if ($type == 'port_etherlike' && ! LibrenmsConfig::get('enable_ports_etherlike')) {
                    return false;
                }

                return $exists[$suffix] ??= Rrd::checkRrdExists(Rrd::name($device->hostname, Rrd::portName($port->port_id, $suffix)));
            })
            ->map(fn ($graph) => $graph[0])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function realtime(Device $device, Request $request): array
    {
        $interval = $request->string('interval')->value();

        return [
            'interval' => in_array($interval, self::REALTIME_INTERVALS) ? $interval : ($device->os == 'linux' ? '15' : '2'),
            'intervals' => self::REALTIME_INTERVALS,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Sensor, Device>
     */
    private function sensors(Device $device, Port $port)
    {
        return $device->sensors()
            ->where('entPhysicalIndex', $port->ifIndex)
            ->where('entPhysicalIndex_measured', 'ports')
            ->orderBy('sensor_type');
    }

    /**
     * Vlans of the port, each with the ports of the device in that vlan
     *
     * @return array<int, array<string, mixed>>
     */
    private function vlans(Device $device, Port $port): array
    {
        $names = $device->vlans()->pluck('vlan_name', 'vlan_vlan');
        $members = $device->portsVlan()->with('port')->get()->groupBy('vlan');

        return $port->vlans()->orderBy('vlan')->get()->map(function (PortVlan $vlan) use ($device, $names, $members) {
            $ports = collect($members[$vlan->vlan] ?? [])
                ->filter(fn (PortVlan $member) => $member->port !== null)
                ->map(fn (PortVlan $member) => ['port' => $member->port, 'untagged' => (bool) $member->untagged]);

            // devices that do not report untagged membership have it in ifVlan
            if (! $ports->contains('untagged', true)) {
                $ports = $ports->merge($device->ports()->where('ifVlan', $vlan->vlan)->get()->map(fn (Port $member) => ['port' => $member, 'untagged' => true]));
            }

            return [
                'vlan' => $vlan,
                'name' => $names[$vlan->vlan] ?? null,
                'ports' => $ports->keyBy('port.port_id')->sortBy('port.ifIndex')->values(),
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function macAccounting(Port $port, Request $request): array
    {
        $subview = $request->string('subview')->value();
        $subview = in_array($subview, ['minigraphs', 'top10']) ? $subview : 'graphs';
        $graph = $request->string('graph')->value() == 'pkts' ? 'pkts' : 'bits';

        if ($subview == 'top10') {
            $sort = $request->string('sort')->value();
            $period = $request->string('period')->value();

            return [
                'subview' => $subview,
                'graph' => $graph,
                'sort' => in_array($sort, ['in', 'out', 'both']) ? $sort : 'in',
                'period' => in_array($period, ['1d', '2d', '1w', '1m', '1y']) ? $period : '1d',
            ];
        }

        $peers = $port->device->bgppeers()->get()->keyBy('bgpPeerIdentifier');

        return [
            'subview' => $subview,
            'graph' => $graph,
            'macs' => $port->macAccounting()
                ->orderByRaw('bps_in + bps_out DESC')
                ->get()
                ->map(function (MacAccounting $mac) use ($peers) {
                    $ip = Ipv4Mac::where('mac_address', $mac->mac)->value('ipv4_address');
                    $peer = $ip ? $peers->get($ip) : null;

                    return [
                        'mac' => $mac,
                        'ip' => $ip,
                        'port' => $ip ? PortCache::getByIp($ip) : null,
                        'as' => $peer ? "AS$peer->bgpPeerRemoteAs" : null,
                        'astext' => $peer?->astext,
                    ];
                }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function atmVps(Port $port, Request $request): array
    {
        $graph = $request->string('graph')->value();

        return [
            'graph' => isset(self::ATM_GRAPHS[$graph]) ? $graph : 'bits',
            'vps' => JuniAtmVp::where('port_id', $port->port_id)->orderBy('vp_id')->get(),
        ];
    }

    /**
     * Rendered output of plugins that hook into the port page
     *
     * @return string[]
     */
    private function plugins(Device $device, Port $port): array
    {
        return array_filter([
            Plugins::call('port_container', [$device, $port]),
            ...\PluginManager::call(PortTabHook::class, ['port' => $port]),
        ]);
    }

    /**
     * @return array{text: string, link: string}|null
     */
    private function billLink(Port $port): ?array
    {
        if (! LibrenmsConfig::get('enable_billing')) {
            return null;
        }

        if ($port->bills->count() == 1) {
            $bill = $port->bills->first();

            return Gate::allows('view', $bill) ? ['text' => __('View Bill'), 'link' => route('bill.show', $bill->bill_id)] : null;
        }

        if ($port->bills->isNotEmpty()) {
            return ['text' => __('View Bills'), 'link' => route('bills.index')];
        }

        return Gate::allows('create', Bill::class) ? ['text' => __('Create Bill'), 'link' => route('bills.index', ['port' => $port->port_id])] : null;
    }
}
