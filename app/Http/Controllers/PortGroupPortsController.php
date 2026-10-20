<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Util\Url;

class PortGroupPortsController extends Controller
{
    /**
     * Incoming sort key => ports column
     *
     * @var array<string, string>
     */
    private const SORT_FIELDS = [
        'device' => 'device',
        'ifName' => 'ifName',
        'ifOperStatus' => 'ifOperStatus',
        'ifSpeed' => 'ifSpeed',
        'ifAlias' => 'ifAlias',
    ];

    private const MEMBERSHIP = ['members', 'available', 'all'];

    public function index(Request $request, PortGroup $portGroup): View
    {
        $this->authorize('update', $portGroup);
        $request->validate(Port::filterValidationRules());

        $members = $this->memberCount($request, $portGroup);

        return view('port-group.ports', [
            'port_group' => $portGroup,
            'members' => $members,
            'membership' => $members > 0 ? 'members' : 'available',
            'filter' => $request->array('filter'),
            'filter_fields' => $this->filterFields(),
        ]);
    }

    public function ports(Request $request, PortGroup $portGroup): JsonResponse
    {
        $this->authorize('update', $portGroup);

        $validated = $request->validate(Port::filterValidationRules() + [
            'membership' => 'nullable|in:' . implode(',', self::MEMBERSHIP),
            'sort' => 'nullable|in:' . implode(',', array_keys(self::SORT_FIELDS)),
            'order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,250',
        ]);

        $membership = $validated['membership'] ?? 'members';
        $query = $this->filteredQuery($request, $portGroup, $membership)
            ->with('device')
            // membership only varies on the all tab
            ->when($membership === 'all', fn (Builder $q) => $q->withExists(['groups as member' => fn (Builder $q) => $q->where('port_groups.id', $portGroup->id)]));

        $sort = self::SORT_FIELDS[$validated['sort'] ?? 'device'];
        $order = $validated['order'] ?? 'asc';
        if ($sort === 'device') {
            // sort by the displayed device name
            $query->orderBy(Device::select('display')->whereColumn('devices.device_id', 'ports.device_id'), $order);
        } else {
            $query->orderBy($sort, $order);
        }
        $query->orderBy('ports.device_id')->orderBy('ifIndex');

        $perPage = $validated['per_page'] ?? 50;
        $paginator = $query->paginate($perPage, ['*'], 'page', $validated['page'] ?? 1);
        if ($paginator->isEmpty() && $paginator->currentPage() > $paginator->lastPage()) {
            // the requested page no longer exists, for example after removing ports, show the last page instead
            $paginator = $query->paginate($perPage, ['*'], 'page', $paginator->lastPage());
        }

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (Port $port) => $this->formatPort($port, $membership)),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'members' => $this->memberCount($request, $portGroup),
        ]);
    }

    public function update(Request $request, PortGroup $portGroup): JsonResponse
    {
        $this->authorize('update', $portGroup);

        $validated = $request->validate(Port::filterValidationRules() + [
            'action' => 'required|in:add,remove',
            'membership' => 'nullable|in:' . implode(',', self::MEMBERSHIP),
            'all' => 'required_without:ids|boolean',
            'ids' => 'required_unless:all,true|array',
            'ids.*' => 'integer',
        ]);

        // either every port matching the filter or the selected ports, always limited to ports the user can access
        $query = empty($validated['all'])
            ? Port::hasAccess($request->user())->whereIn('ports.port_id', $validated['ids'])
            : $this->filteredQuery($request, $portGroup, $validated['membership'] ?? 'members');

        $count = 0;
        $query->select('ports.port_id')->chunkById(1000, function ($ports) use ($portGroup, $validated, &$count): void {
            $portIds = $ports->pluck('port_id');
            $count += $validated['action'] === 'add'
                ? count($portGroup->ports()->syncWithoutDetaching($portIds)['attached'])
                : $portGroup->ports()->detach($portIds);
        }, 'ports.port_id', 'port_id');

        return response()->json([
            'message' => trans_choice($validated['action'] === 'add' ? 'port.groups.added' : 'port.groups.removed', $count),
            'updated' => $count,
            'members' => $this->memberCount($request, $portGroup),
        ]);
    }

    /**
     * @return Builder<Port>
     */
    private function filteredQuery(Request $request, PortGroup $portGroup, string $membership): Builder
    {
        return Port::hasAccess($request->user())
            ->when($membership === 'members', fn (Builder $q) => $q->inPortGroup($portGroup->id))
            ->when($membership === 'available', fn (Builder $q) => $q->notInPortGroup($portGroup->id))
            ->applyFilters($request->array('filter'));
    }

    /**
     * Group members the user can access, matching the members tab
     */
    private function memberCount(Request $request, PortGroup $portGroup): int
    {
        return Port::hasAccess($request->user())->inPortGroup($portGroup->id)->count();
    }

    /**
     * @return list<array{key: string, label: string, type: string, search?: bool, endpoint?: string, options?: array<string, string>, params?: array<string, string>}>
     */
    private function filterFields(): array
    {
        return [
            ['key' => 'search', 'label' => __('port.settings.filter_search'), 'type' => 'text', 'search' => true],
            ['key' => 'device_id', 'label' => __('Device'), 'type' => 'select', 'endpoint' => route('ajax.select.device')],
            ['key' => 'device.hostname', 'label' => __('Hostname'), 'type' => 'text'],
            ['key' => 'device.groups.id', 'label' => __('device.device_group'), 'type' => 'select', 'endpoint' => route('ajax.select.device-group')],
            ['key' => 'device.location_id', 'label' => __('Location'), 'type' => 'select', 'endpoint' => route('ajax.select.location')],
            ['key' => 'groups.id', 'label' => __('port.port_group'), 'type' => 'select', 'endpoint' => route('ajax.select.port-group')],
            ['key' => 'state', 'label' => __('port.oper_status'), 'type' => 'select', 'options' => [
                'up' => __('Up'),
                'down' => __('Down'),
                'shutdown' => __('Shutdown'),
            ]],
            ['key' => 'ifSpeed', 'label' => __('port.speed'), 'type' => 'select', 'endpoint' => route('ajax.select.port-field'), 'params' => ['field' => 'ifSpeed']],
            ['key' => 'ifType', 'label' => __('port.media'), 'type' => 'select', 'endpoint' => route('ajax.select.port-field'), 'params' => ['field' => 'ifType']],
            ['key' => 'disabled', 'label' => __('Disabled'), 'type' => 'boolean'],
            ['key' => 'ignore', 'label' => __('Ignored'), 'type' => 'boolean'],
            ['key' => 'deleted', 'label' => __('Deleted'), 'type' => 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPort(Port $port, string $membership): array
    {
        return [
            'port_id' => $port->port_id,
            'label' => $port->getLabel(),
            'ifDescr' => $port->ifDescr,
            'ifAlias' => $port->ifAlias,
            'ifSpeed' => $port->ifSpeed,
            'url' => Url::portUrl($port),
            'link_class' => Url::portLinkDisplayClass($port),
            'ifAdminStatus' => $port->ifAdminStatus?->value,
            'ifOperStatus' => $port->ifOperStatus?->value,
            'deleted' => (bool) $port->deleted,
            'device_id' => $port->device_id,
            'device' => $port->device?->display,
            'device_url' => $port->device ? Url::deviceUrl($port->device) : null,
            'member' => $membership === 'all' ? (bool) $port->getAttribute('member') : $membership === 'members',
        ];
    }
}
