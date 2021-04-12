<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Alerting\QueryBuilderFilter;
use LibreNMS\Alerting\QueryBuilderFluentParser;
use Toastr;

class DeviceGroupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(DeviceGroup::class, 'device_group');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('manage', DeviceGroup::class);

        $ungrouped_devices = Device::orderBy('hostname')->whereNotIn('device_id', function ($query) {
            $query->select('device_id')->from('device_group_device');
        })->get();

        return view('device-group.index', [
            'device_groups' => DeviceGroup::orderBy('name')->withCount('devices')->get(),
            'ungrouped_devices' => $ungrouped_devices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('device-group.create', [
            'device_group' => new DeviceGroup(),
            'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:device_groups',
            'type' => 'required|in:dynamic,static',
            'devices' => 'array|required_if:type,static',
            'devices.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $deviceGroup = DeviceGroup::make($request->only(['name', 'desc', 'type']));
        $deviceGroup->rules = json_decode($request->rules);
        $deviceGroup->save();

        if ($request->type == 'static') {
            $deviceGroup->devices()->sync($request->devices);
        }

        Toastr::success(__('Device Group :name created', ['name' => $deviceGroup->name]));

        return redirect()->route('device-groups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(DeviceGroup $deviceGroup)
    {
        return redirect(url('/devices/group=' . $deviceGroup->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\View\View
     */
    public function edit(DeviceGroup $deviceGroup)
    {
        // convert old rules on edit
        if (is_null($deviceGroup->rules)) {
            $query_builder = QueryBuilderFluentParser::fromOld($deviceGroup->pattern);
            $deviceGroup->rules = $query_builder->toArray();
        }

        return view('device-group.edit', [
            'device_group' => $deviceGroup,
            'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, DeviceGroup $deviceGroup)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                Rule::unique('device_groups')->where(function ($query) use ($deviceGroup) {
                    $query->where('id', '!=', $deviceGroup->id);
                }),
            ],
            'type' => 'required|in:dynamic,static',
            'devices' => 'array|required_if:type,static',
            'devices.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $deviceGroup->fill($request->only(['name', 'desc', 'type']));

        $devices_updated = false;
        if ($deviceGroup->type == 'static') {
            // sync device_ids from input
            $updated = $deviceGroup->devices()->sync($request->get('devices', []));
            // check for attached/detached/updated
            $devices_updated = array_sum(array_map(function ($device_ids) {
                return count($device_ids);
            }, $updated)) > 0;
        } else {
            $deviceGroup->rules = json_decode($request->rules);
        }

        if ($deviceGroup->isDirty() || $devices_updated) {
            try {
                if ($deviceGroup->save() || $devices_updated) {
                    Toastr::success(__('Device Group :name updated', ['name' => $deviceGroup->name]));
                } else {
                    Toastr::error(__('Failed to save'));

                    return redirect()->back()->withInput();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                return redirect()->back()->withInput()->withErrors([
                    'rules' => __('Rules resulted in invalid query: ') . $e->getMessage(),
                ]);
            }
        } else {
            Toastr::info(__('No changes made'));
        }

        return redirect()->route('device-groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeviceGroup $deviceGroup)
    {
        if ($deviceGroup->serviceTemplates()->exists()) {
            $msg = __('Device Group :name still has Service Templates associated with it. Please remove or update the Service Template accordingly', ['name' => $deviceGroup->name]);

            return response($msg, 200);
        }
        $deviceGroup->delete();

        $msg = __('Device Group :name deleted', ['name' => $deviceGroup->name]);

        return response($msg, 200);
    }
}
