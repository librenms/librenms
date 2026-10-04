<?php

/**
 * EditPortsController.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\PortGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Enum\Severity;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\Url;

class EditPortsController
{
    use AuthorizesRequests;

    /**
     * Incoming sort key => ports column
     *
     * @var array<string, string>
     */
    private const SORT_FIELDS = [
        'ifIndex' => 'ifIndex',
        'ifName' => 'ifName',
        'ifOperStatus' => 'ifOperStatus',
        'ifSpeed' => 'ifSpeed',
        'ifAlias' => 'ifAlias',
    ];

    /**
     * Bulk action => [ports column, value]
     *
     * @var array<string, array{string, int}>
     */
    private const BULK_UPDATES = [
        'disable' => ['disabled', 1],
        'enable' => ['disabled', 0],
        'ignore' => ['ignore', 1],
        'unignore' => ['ignore', 0],
    ];

    private const POLLING_STATES = ['polled', 'not_polled', 'skipped'];

    public function index(Request $request, Device $device): View
    {
        $this->authorize('update', $device);
        $request->validate(Port::filterValidationRules());

        $selectedPorts = $device->selectedPortPolling();

        return view('device.edit.ports', [
            'device' => $device,
            'selected_ports' => $selectedPorts,
            'ports_module' => $this->portsModuleStatus($device),
            'summary' => $this->summary($device, $selectedPorts->isEnabled()),
            'filter' => $request->array('filter'),
            'filter_fields' => $this->filterFields($device),
            'can_create_group' => $request->user()->can('create', PortGroup::class),
        ]);
    }

    public function ports(Request $request, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate(Port::filterValidationRules() + [
            'sort' => 'nullable|in:' . implode(',', array_keys(self::SORT_FIELDS)),
            'order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,250',
        ]);

        $selected = $device->selectedPortPolling()->isEnabled();
        $query = $this->filteredQuery($device, $request->array('filter'), $selected)->with('groups:id,name');

        $sort = self::SORT_FIELDS[$validated['sort'] ?? 'ifIndex'];
        $query->orderBy($sort, $validated['order'] ?? 'asc');
        if ($sort !== 'ifIndex') {
            $query->orderBy('ifIndex');
        }

        $perPage = $validated['per_page'] ?? 50;
        $paginator = $query->paginate($perPage, ['*'], 'page', $validated['page'] ?? 1);
        if ($paginator->isEmpty() && $paginator->currentPage() > $paginator->lastPage()) {
            // the requested page no longer exists, for example after a bulk change, show the last page instead
            $paginator = $query->paginate($perPage, ['*'], 'page', $paginator->lastPage());
        }
        $attribs = $device->getAttribs();

        return response()->json([
            'ports' => collect($paginator->items())->map(fn (Port $port) => $this->formatPort($port, $attribs, $selected)),
            'page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'summary' => $this->summary($device, $selected),
        ]);
    }

    public function update(Request $request, Device $device, Port $port): JsonResponse
    {
        $this->authorize('update', $device);
        $this->authorize('update', $port);

        $validated = $request->validate([
            'disabled' => 'sometimes|boolean',
            'ignore' => 'sometimes|boolean',
            'ifAlias' => 'sometimes|nullable|string|max:255',
            'ifSpeed' => 'sometimes|nullable|integer|min:1',
            'port_descr_speed' => 'sometimes|nullable|array',
            'port_descr_speed.out' => 'required_with:port_descr_speed|integer|min:1',
            'port_descr_speed.in' => 'required_with:port_descr_speed|integer|min:1',
            'rrd_tune' => 'sometimes|boolean',
        ]);

        if (empty($validated)) {
            return response()->json(['message' => __('Nothing to update')], 422);
        }

        if (array_key_exists('disabled', $validated)) {
            $port->disabled = (bool) $validated['disabled'];
        }

        if (array_key_exists('ignore', $validated)) {
            $port->ignore = (bool) $validated['ignore'];
        }

        if (array_key_exists('ifAlias', $validated)) {
            $this->updateAlias($device, $port, $validated['ifAlias']);
        }

        if (array_key_exists('ifSpeed', $validated)) {
            $this->updateSpeed($device, $port, isset($validated['ifSpeed']) ? (int) $validated['ifSpeed'] : null);
        }

        if (array_key_exists('port_descr_speed', $validated)) {
            $this->updateCircuitSpeed($device, $port, $validated['port_descr_speed']);
        }

        if (array_key_exists('rrd_tune', $validated)) {
            $device->setAttrib('ifName_tune:' . $port->ifName, $validated['rrd_tune'] ? 'true' : 'false');
        }

        $port->save();

        $selected = $device->selectedPortPolling()->isEnabled();

        return response()->json([
            'message' => __('Port :port updated', ['port' => $port->getLabel()]),
            'port' => $this->formatPort($port->load('groups'), $device->getAttribs(), $selected),
            'summary' => $this->summary($device, $selected),
        ]);
    }

    public function bulk(Request $request, Device $device): JsonResponse
    {
        $this->authorize('update', $device);
        $this->authorize('port.update');

        $validated = $request->validate(Port::filterValidationRules() + [
            'action' => 'required|in:' . implode(',', [...array_keys(self::BULK_UPDATES), 'add_group', 'remove_group']),
            'group_id' => 'nullable|required_if:action,add_group,remove_group|integer',
            'all' => 'required_without:ports|boolean',
            'ports' => 'required_without:all|array',
            'ports.*' => 'integer',
        ]);

        // either every port matching the filter or the selected ports, always limited to this device
        $selected = $device->selectedPortPolling()->isEnabled();
        $query = empty($validated['all'])
            ? $device->ports()->whereIn('port_id', $validated['ports'])
            : $this->filteredQuery($device, $request->array('filter'), $selected);

        if (isset(self::BULK_UPDATES[$validated['action']])) {
            [$field, $value] = self::BULK_UPDATES[$validated['action']];
            $count = $query->where($field, '!=', $value)->update([$field => $value]);
        } else {
            $group = PortGroup::hasAccess($request->user())->findOrFail($validated['group_id']);
            $portIds = $query->pluck('port_id');
            $count = $validated['action'] === 'add_group'
                ? count($group->ports()->syncWithoutDetaching($portIds)['attached'])
                : $group->ports()->detach($portIds);
        }

        return response()->json([
            'message' => trans_choice('{0} No ports changed|{1} :count port updated|[2,*] :count ports updated', $count),
            'updated' => $count,
            'summary' => $this->summary($device, $selected),
        ]);
    }

    public function settings(Request $request, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'selected_ports' => 'required|in:true,false,clear',
        ]);

        if ($validated['selected_ports'] === 'clear') {
            $device->forgetAttrib('selected_ports');
        } else {
            $device->setAttrib('selected_ports', $validated['selected_ports']);
        }

        $selectedPorts = $device->selectedPortPolling();

        return response()->json([
            'message' => __('Selected port polling updated'),
            'selected_ports' => $selectedPorts,
            'summary' => $this->summary($device, $selectedPorts->isEnabled()),
        ]);
    }

    public function resetState(Device $device): JsonResponse
    {
        $this->authorize('update', $device);
        $this->authorize('port.update');

        $device->ports()->update([
            'ifSpeed_prev' => null,
            'ifOperStatus_prev' => null,
            'ifAdminStatus_prev' => null,
        ]);

        Eventlog::log('Port state history reset by ' . Auth::user()?->username, $device);

        return response()->json([
            'message' => __('Port state history cleared'),
        ]);
    }

    private function portsModuleStatus(Device $device): ModuleStatus
    {
        $deviceSetting = $device->getAttrib('poll_ports');

        return new ModuleStatus(
            (bool) LibrenmsConfig::get('poller_modules.ports', false),
            LibrenmsConfig::has("os.$device->os.poller_modules.ports") ? (bool) LibrenmsConfig::get("os.$device->os.poller_modules.ports") : null,
            $deviceSetting === null ? null : (bool) $deviceSetting,
        );
    }

    /**
     * @return list<array{key: string, label: string, type: string, search?: bool, endpoint?: string, options?: array<string, string>, params?: array<string, string|int>}>
     */
    private function filterFields(Device $device): array
    {
        return [
            ['key' => 'search', 'label' => __('Name or description'), 'type' => 'text', 'search' => true],
            ['key' => 'polling', 'label' => __('Polling'), 'type' => 'select', 'options' => [
                'polled' => __('Polled'),
                'not_polled' => __('Not polled'),
                'skipped' => __('Skipped while down'),
            ]],
            ['key' => 'state', 'label' => __('port.oper_status'), 'type' => 'select', 'options' => [
                'up' => __('Up'),
                'down' => __('Down'),
                'shutdown' => __('Shutdown'),
            ]],
            ['key' => 'disabled', 'label' => __('Polling disabled'), 'type' => 'boolean'],
            ['key' => 'ignore', 'label' => __('Ignored'), 'type' => 'boolean'],
            ['key' => 'deleted', 'label' => __('Deleted'), 'type' => 'boolean'],
            ['key' => 'groups.id', 'label' => __('port.port_group'), 'type' => 'select', 'endpoint' => route('ajax.select.port-group')],
            ['key' => 'ifSpeed', 'label' => __('port.speed'), 'type' => 'select', 'endpoint' => route('ajax.select.port-field'), 'params' => ['field' => 'ifSpeed', 'device' => $device->device_id]],
            ['key' => 'ifType', 'label' => __('port.media'), 'type' => 'select', 'endpoint' => route('ajax.select.port-field'), 'params' => ['field' => 'ifType', 'device' => $device->device_id]],
            ['key' => 'ifIndex', 'label' => __('Index'), 'type' => 'number'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return HasMany<Port, Device>
     */
    private function filteredQuery(Device $device, array $filter, bool $selected): HasMany
    {
        $query = $device->ports();

        // polling state depends on the device selected port polling setting, so it is handled here
        foreach ((array) ($filter['polling'] ?? []) as $op => $value) {
            if (! in_array($op, ['eq', 'neq', 'in', 'not_in'])) {
                continue;
            }

            $states = array_intersect(explode(',', (string) $value), self::POLLING_STATES);
            $query->{in_array($op, ['neq', 'not_in']) ? 'whereNot' : 'where'}(function (Builder $query) use ($states, $selected): void {
                foreach ($states as $state) {
                    $query->orWhere(fn (Builder $q) => $this->wherePollingState($q, $state, $selected));
                }
            });
        }
        unset($filter['polling']);

        return $query->applyFilters($filter);
    }

    /**
     * SQL version of pollingState(), mirrors how the ports poller decides which ports to poll
     *
     * @param  Builder<Port>  $query
     * @return Builder<Port>
     */
    private function wherePollingState(Builder $query, string $state, bool $selected): Builder
    {
        // selected port polling skips ports that are down, null safe so NOT() works
        $isUp = fn (Builder $q) => $q
            ->where(fn (Builder $q) => $q->whereNull('ifAdminStatus')->orWhere('ifAdminStatus', '!=', IfOperStatus::Down))
            ->where(fn (Builder $q) => $q->whereNull('ifOperStatus')->orWhereNotIn('ifOperStatus', [IfOperStatus::Down, IfOperStatus::LowerLayerDown]));

        return match ($state) {
            'polled' => $query->where('deleted', 0)->where('disabled', 0)->when($selected, $isUp),
            'skipped' => $query->where('deleted', 0)->where('disabled', 0)
                ->when($selected, fn (Builder $q) => $q->whereNot($isUp), fn (Builder $q) => $q->whereRaw('1 = 0')),
            default => $query->whereNot(fn (Builder $q) => $this->wherePollingState($q, 'polled', $selected)), // not_polled
        };
    }

    /**
     * Port counts, deleted ports are excluded from every count except deleted
     *
     * @return array{polled: int, skipped: int, disabled: int, ignored: int, deleted: int}
     */
    private function summary(Device $device, bool $selected): array
    {
        $count = fn (callable $where) => $device->ports()->where('deleted', 0)->where($where)->count();

        return [
            'polled' => $count(fn (Builder $q) => $this->wherePollingState($q, 'polled', $selected)),
            'skipped' => $selected ? $count(fn (Builder $q) => $this->wherePollingState($q, 'skipped', $selected)) : 0,
            'disabled' => $count(fn (Builder $q) => $q->where('disabled', 1)),
            'ignored' => $count(fn (Builder $q) => $q->where('ignore', 1)),
            'deleted' => $device->ports()->where('deleted', 1)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attribs
     * @return array<string, mixed>
     */
    private function formatPort(Port $port, array $attribs, bool $selected): array
    {
        return [
            'port_id' => $port->port_id,
            'ifIndex' => $port->ifIndex,
            'label' => $port->getLabel(),
            'ifDescr' => $port->ifDescr,
            'url' => Url::portUrl($port),
            'ifAdminStatus' => $port->ifAdminStatus?->value,
            'ifOperStatus' => $port->ifOperStatus?->value,
            'disabled' => (bool) $port->disabled,
            'ignore' => (bool) $port->ignore,
            'deleted' => (bool) $port->deleted,
            'polling' => $this->pollingState($port, $selected),
            'ifSpeed' => $port->ifSpeed,
            'ifSpeed_override' => isset($attribs['ifSpeed:' . $port->ifName]),
            // the speed reported by the device is not stored while overridden
            'ifSpeed_device' => isset($attribs['ifSpeed:' . $port->ifName]) ? null : $port->ifSpeed,
            'circuit_speed' => $port->circuitSpeeds(),
            'circuit_speed_override' => isset($attribs['port_descr_speed:' . $port->ifName]),
            'ifAlias' => $port->ifAlias === 'repoll' ? '' : $port->ifAlias,
            'ifAlias_override' => isset($attribs['ifName:' . $port->ifName]),
            'rrd_tune' => ($attribs['ifName_tune:' . $port->ifName] ?? null) === 'true',
            'groups' => $port->groups->map(fn (PortGroup $group) => ['id' => $group->id, 'text' => $group->name])->values(),
        ];
    }

    /**
     * Mirrors how the ports poller decides which ports to poll
     */
    private function pollingState(Port $port, bool $selected): string
    {
        if ($port->deleted) {
            return 'deleted';
        }

        if ($port->disabled) {
            return 'disabled';
        }

        if ($selected) {
            if ($port->ifAdminStatus === IfOperStatus::Down) {
                return 'admin_down';
            }

            if ($port->ifOperStatus === IfOperStatus::Down || $port->ifOperStatus === IfOperStatus::LowerLayerDown) {
                return 'down';
            }
        }

        return 'polled';
    }

    private function updateAlias(Device $device, Port $port, ?string $alias): void
    {
        if ($alias === null || $alias === '') {
            // repoll prevents the poller from falling back to ifDescr
            $port->ifAlias = 'repoll';
            $device->forgetAttrib('ifName:' . $port->ifName);
            Eventlog::log("$port->ifName Port ifAlias cleared manually", $device, 'interface', Severity::Notice, $port->port_id);

            return;
        }

        $port->ifAlias = $alias;
        $device->setAttrib('ifName:' . $port->ifName, 1);
        Eventlog::log("$port->ifName Port ifAlias set manually: $alias", $device, 'interface', Severity::Notice, $port->port_id);
    }

    private function updateSpeed(Device $device, Port $port, ?int $speed): void
    {
        if ($speed === null) {
            // the next poll sets the speed reported by the device
            if ($device->forgetAttrib('ifSpeed:' . $port->ifName)) {
                Eventlog::log("$port->ifName Port speed cleared manually", $device, 'interface', Severity::Notice, $port->port_id);
            }

            return;
        }

        $port->ifSpeed = $speed;
        $device->setAttrib('ifSpeed:' . $port->ifName, $speed);
        Eventlog::log("$port->ifName Port speed set manually: $speed", $device, 'interface', Severity::Notice, $port->port_id);

        $portTune = $device->getAttrib('ifName_tune:' . $port->ifName);
        $deviceTune = $device->getAttrib('override_rrdtool_tune');
        if ($portTune == 'true'
            || ($deviceTune == 'true' && $portTune != 'false')
            || (LibrenmsConfig::get('rrdtool_tune') && $portTune != 'false' && $deviceTune != 'false')) {
            Rrd::tune('port', Rrd::name($device->hostname, Rrd::portName($port->port_id)), $speed);
        }
    }

    /**
     * @param  array{out: int, in: int}|null  $speeds
     */
    private function updateCircuitSpeed(Device $device, Port $port, ?array $speeds): void
    {
        if ($speeds === null) {
            // the next poll sets the speed from the port description
            if ($device->forgetAttrib('port_descr_speed:' . $port->ifName)) {
                Eventlog::log("$port->ifName Port circuit speed cleared manually", $device, 'interface', Severity::Notice, $port->port_id);
            }

            return;
        }

        // same format as the port description, egress/ingress
        $speed = $this->compactSpeed((int) $speeds['out']);
        if ($speeds['in'] != $speeds['out']) {
            $speed .= '/' . $this->compactSpeed((int) $speeds['in']);
        }

        $port->port_descr_speed = $speed;
        $device->setAttrib('port_descr_speed:' . $port->ifName, $speed);
        Eventlog::log("$port->ifName Port circuit speed set manually: $speed", $device, 'interface', Severity::Notice, $port->port_id);
    }

    /**
     * Lossless short SI format, for example 100M or 1.544M
     */
    private function compactSpeed(int $bps): string
    {
        foreach (['T' => 1_000_000_000_000, 'G' => 1_000_000_000, 'M' => 1_000_000, 'k' => 1_000] as $prefix => $size) {
            if ($bps >= $size && $bps % intdiv($size, 1000) === 0) {
                return round($bps / $size, 3) . $prefix;
            }
        }

        return (string) $bps;
    }
}
