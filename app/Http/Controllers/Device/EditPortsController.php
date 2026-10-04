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
    private const BULK_ACTIONS = [
        'disable' => ['disabled', 1],
        'enable' => ['disabled', 0],
        'ignore' => ['ignore', 1],
        'unignore' => ['ignore', 0],
    ];

    private const FILTERS = ['all', 'polled', 'not_polled', 'skipped', 'up', 'down', 'admin_down', 'disabled', 'ignored', 'deleted'];

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        return view('device.edit.ports', [
            'device' => $device,
            'selected_ports' => $this->selectedPortsStatus($device),
            'ports_module' => $this->portsModuleStatus($device),
            'summary' => $this->summary($device),
        ]);
    }

    public function ports(Request $request, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate($this->filterRules() + [
            'sort' => 'nullable|in:' . implode(',', array_keys(self::SORT_FIELDS)),
            'order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,1000',
        ]);

        $selected = $this->selectedPortsStatus($device)->isEnabled();
        $query = $this->filteredQuery($device, $validated, $selected)->with('groups');

        $sort = self::SORT_FIELDS[$validated['sort'] ?? 'ifIndex'];
        $query->orderBy($sort, $validated['order'] ?? 'asc');
        if ($sort !== 'ifIndex') {
            $query->orderBy('ifIndex');
        }

        $paginator = $query->paginate($validated['per_page'] ?? 50, ['*'], 'page', $validated['page'] ?? 1);
        $attribs = $device->getAttribs();

        return response()->json([
            'ports' => collect($paginator->items())->map(fn (Port $port) => $this->formatPort($port, $attribs, $selected)),
            'page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'summary' => $this->summary($device),
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
            'ifSpeed' => 'sometimes|nullable|integer|min:0',
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

        if (array_key_exists('rrd_tune', $validated)) {
            $device->setAttrib('ifName_tune:' . $port->ifName, $validated['rrd_tune'] ? 'true' : 'false');
        }

        $port->save();

        $selected = $this->selectedPortsStatus($device)->isEnabled();

        return response()->json([
            'message' => __('Port :port updated', ['port' => $port->getLabel()]),
            'port' => $this->formatPort($port->load('groups'), $device->getAttribs(), $selected),
            'summary' => $this->summary($device),
        ]);
    }

    public function bulk(Request $request, Device $device): JsonResponse
    {
        $this->authorize('update', $device);
        $this->authorize('port.update');

        $validated = $request->validate($this->filterRules() + [
            'action' => 'required|in:' . implode(',', array_keys(self::BULK_ACTIONS)),
        ]);

        [$field, $value] = self::BULK_ACTIONS[$validated['action']];

        $selected = $this->selectedPortsStatus($device)->isEnabled();
        $count = $this->filteredQuery($device, $validated, $selected)
            ->where($field, '!=', $value)
            ->update([$field => $value]);

        return response()->json([
            'message' => trans_choice('{0} No ports changed|{1} :count port updated|[2,*] :count ports updated', $count),
            'updated' => $count,
            'summary' => $this->summary($device),
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

        return response()->json([
            'message' => __('Selected port polling updated'),
            'selected_ports' => $this->selectedPortsStatus($device),
            'summary' => $this->summary($device),
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

    /**
     * The poller uses the first setting found: device override, os setting, then global setting
     */
    private function selectedPortsStatus(Device $device): ModuleStatus
    {
        $deviceSetting = $device->getAttrib('selected_ports');

        return new ModuleStatus(
            (bool) LibrenmsConfig::get('polling.selected_ports', false),
            LibrenmsConfig::has("os.$device->os.polling.selected_ports") ? (bool) LibrenmsConfig::get("os.$device->os.polling.selected_ports") : null,
            $deviceSetting === null ? null : $deviceSetting === 'true',
        );
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
     * @return array<string, string>
     */
    private function filterRules(): array
    {
        return [
            'search' => 'nullable|string',
            'filter' => 'nullable|in:' . implode(',', self::FILTERS),
        ];
    }

    /**
     * @param  array{search?: ?string, filter?: ?string}  $params
     * @return HasMany<Port, Device>
     */
    private function filteredQuery(Device $device, array $params, bool $selected): HasMany
    {
        $query = $device->ports();

        if (! empty($params['search'])) {
            $search = '%' . $params['search'] . '%';
            $query->where(function (Builder $query) use ($params, $search): void {
                $query->where('ifName', 'like', $search)
                    ->orWhere('ifDescr', 'like', $search)
                    ->orWhere('ifAlias', 'like', $search);

                if (ctype_digit($params['search'])) {
                    $query->orWhere('ifIndex', $params['search']);
                }
            });
        }

        $filter = $params['filter'] ?? 'all';
        if ($filter === 'polled') {
            $query->where('deleted', 0)->where('disabled', 0)
                ->when($selected, fn ($q) => $this->whereNotDown($q));
        } elseif ($filter === 'not_polled') {
            $query->where(fn ($q) => $q->where('deleted', 1)->orWhere('disabled', 1)
                ->when($selected, fn ($q) => $q->orWhere(fn ($q) => $this->whereDown($q))));
        } elseif ($filter === 'skipped') {
            $query->where('deleted', 0)->where('disabled', 0)
                ->where(fn ($q) => $selected ? $this->whereDown($q) : $q->whereRaw('1 = 0'));
        } elseif ($filter === 'up') {
            $query->where('ifOperStatus', IfOperStatus::Up);
        } elseif ($filter === 'down') {
            $query->where('ifAdminStatus', IfOperStatus::Up)->where('ifOperStatus', '!=', IfOperStatus::Up);
        } elseif ($filter === 'admin_down') {
            $query->where('ifAdminStatus', IfOperStatus::Down);
        } elseif ($filter === 'disabled') {
            $query->where('disabled', 1);
        } elseif ($filter === 'ignored') {
            $query->where('ignore', 1);
        } elseif ($filter === 'deleted') {
            $query->where('deleted', 1);
        }

        return $query;
    }

    /**
     * @template TQuery of \Illuminate\Contracts\Database\Query\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    private function whereNotDown($query)
    {
        return $query->where(fn ($q) => $q->whereNull('ifAdminStatus')->orWhere('ifAdminStatus', '!=', IfOperStatus::Down))
            ->where(fn ($q) => $q->whereNull('ifOperStatus')->orWhereNotIn('ifOperStatus', [IfOperStatus::Down, IfOperStatus::LowerLayerDown]));
    }

    /**
     * Ports that selected port polling skips because they are down
     *
     * @template TQuery of \Illuminate\Contracts\Database\Query\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    private function whereDown($query)
    {
        return $query->where('ifAdminStatus', IfOperStatus::Down)
            ->orWhereIn('ifOperStatus', [IfOperStatus::Down, IfOperStatus::LowerLayerDown]);
    }

    /**
     * @return array{total: int, polled: int, skipped: int, disabled: int, deleted: int, ignored: int}
     */
    private function summary(Device $device): array
    {
        $selected = $this->selectedPortsStatus($device)->isEnabled();
        $active = fn (): Builder => Port::query()->where('device_id', $device->device_id)->where('deleted', 0)->where('disabled', 0);

        $total = $device->ports()->count();
        $deleted = $device->ports()->where('deleted', 1)->count();
        $disabled = $device->ports()->where('deleted', 0)->where('disabled', 1)->count();
        $skipped = $selected ? $active()->where(fn ($q) => $this->whereDown($q))->count() : 0;

        return [
            'total' => $total,
            'polled' => $total - $deleted - $disabled - $skipped,
            'skipped' => $skipped,
            'disabled' => $disabled,
            'deleted' => $deleted,
            'ignored' => $device->ports()->where('deleted', 0)->where('ignore', 1)->count(),
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
        if (empty($speed)) {
            // the poller will update ifSpeed with the device value
            $device->forgetAttrib('ifSpeed:' . $port->ifName);
            Eventlog::log("$port->ifName Port speed cleared manually", $device, 'interface', Severity::Notice, $port->port_id);

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
}
