<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LibreNMS\Alerting\QueryBuilderFilter;

class DeviceGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', DeviceGroup::class);

        return view('device-group.index', [
            'device_groups' => DeviceGroup::hasAccess($request->user())->orderBy('name')->withCount('devices')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', DeviceGroup::class);

        return view('device-group.create', [
            'device_group' => new DeviceGroup(),
            'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, ToastInterface $toast)
    {
        $this->authorize('create', DeviceGroup::class);

        $this->validate($request, [
            'name' => 'required|string|unique:device_groups',
            'type' => 'required|in:dynamic,static',
            'devices' => 'array|required_if:type,static',
            'devices.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);
        $devices = $request->type == 'static' ? $this->staticMembers($request) : [];

        $deviceGroup = new DeviceGroup($request->only(['name', 'desc', 'type']));
        $deviceGroup->rules = json_decode($request->rules);
        $deviceGroup->save();

        if ($request->type == 'static') {
            $deviceGroup->devices()->sync($devices);
        }

        $toast->success(__('Device Group :name created', ['name' => htmlentities((string) $deviceGroup->name)]));

        return redirect()->route('device-groups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  DeviceGroup  $deviceGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(DeviceGroup $deviceGroup)
    {
        $this->authorize('view', $deviceGroup);

        return redirect(route('devices', ['filter' => ['groups.id' => ['eq' => $deviceGroup->id]]]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  DeviceGroup  $deviceGroup
     * @return \Illuminate\View\View
     */
    public function edit(DeviceGroup $deviceGroup)
    {
        $this->authorize('update', $deviceGroup);

        return view('device-group.edit', [
            'device_group' => $deviceGroup,
            'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  DeviceGroup  $deviceGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, DeviceGroup $deviceGroup, ToastInterface $toast)
    {
        $this->authorize('update', $deviceGroup);

        $this->validate($request, [
            'name' => [
                'required',
                'string',
                Rule::unique('device_groups')->where(function ($query) use ($deviceGroup): void {
                    $query->where('id', '!=', $deviceGroup->id);
                }),
            ],
            'type' => 'required|in:dynamic,static',
            'devices' => 'array|required_if:type,static',
            'devices.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);
        $devices = $request->type == 'static' ? $this->staticMembers($request, $deviceGroup) : [];

        $deviceGroup->fill($request->only(['name', 'desc', 'type']));

        $devices_updated = false;
        if ($deviceGroup->type == 'static') {
            // sync device_ids from input (plus existing members the user cannot see)
            $updated = $deviceGroup->devices()->sync($devices);
            // check for attached/detached/updated
            $devices_updated = array_sum(array_map(count(...), $updated)) > 0;
        } else {
            $deviceGroup->rules = json_decode($request->rules);
        }

        if ($deviceGroup->isDirty() || $devices_updated) {
            try {
                if ($deviceGroup->save() || $devices_updated) {
                    $toast->success(__('Device Group :name updated', ['name' => htmlentities($deviceGroup->name)]));
                } else {
                    $toast->error(__('Failed to save'));

                    return redirect()->back()->withInput();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                return redirect()->back()->withInput()->withErrors([
                    'rules' => __('Rules resulted in invalid query: ') . $e->getMessage(),
                ]);
            }
        } else {
            $toast->info(__('No changes made'));
        }

        return redirect()->route('device-groups.index');
    }

    /**
     * Schedule all devices in the group for rediscovery.
     */
    public function rediscover(Request $request, DeviceGroup $deviceGroup): JsonResponse
    {
        $this->authorize('view', $deviceGroup);
        $this->authorize('update', Device::class);

        // all or nothing: every member device must be accessible to the user
        $members = $deviceGroup->devices()->pluck('devices.device_id');
        $accessible = Device::hasAccess($request->user())->whereIntegerInRaw('device_id', $members)->count();
        abort_if($accessible !== $members->count(), 403);

        Device::whereIntegerInRaw('device_id', $members)->update(['last_discovered' => null]);

        return response()->json([
            'message' => __('Devices of group :name will be rediscovered', ['name' => htmlentities($deviceGroup->name)]),
        ]);
    }

    /**
     * Resolve the device ids to sync to a static group from the request.
     * Users may only add devices they can access. Existing members the user
     * cannot access are always kept, they can neither be added nor removed.
     *
     * @return int[]
     *
     * @throws ValidationException when a device that is not accessible would be added
     */
    private function staticMembers(Request $request, ?DeviceGroup $deviceGroup = null): array
    {
        $requested = $request->collect('devices')->map(fn ($id): int => (int) $id)->unique();
        $current = $deviceGroup?->devices()->pluck('devices.device_id') ?? collect();

        $accessible = Device::hasAccess($request->user())
            ->whereIntegerInRaw('device_id', $requested->merge($current))
            ->pluck('device_id');
        $hidden = $current->diff($accessible);

        if ($requested->diff($accessible)->diff($hidden)->isNotEmpty()) {
            throw ValidationException::withMessages(['devices' => __('validation.exists', ['attribute' => 'devices'])]);
        }

        return $requested->merge($hidden)->unique()->values()->all();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeviceGroup $deviceGroup): JsonResponse
    {
        $this->authorize('delete', $deviceGroup);

        if ($deviceGroup->serviceTemplates()->exists()) {
            return response()->json([
                'message' => __('Device Group :name still has Service Templates associated with it. Please remove or update the Service Template accordingly', ['name' => htmlentities($deviceGroup->name)]),
            ], 422);
        }
        $deviceGroup->delete();

        return response()->json([
            'message' => __('Device Group :name deleted', ['name' => htmlentities($deviceGroup->name)]),
        ]);
    }
}
