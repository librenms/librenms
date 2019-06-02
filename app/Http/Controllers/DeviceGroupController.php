<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use LibreNMS\Alerting\QueryBuilderFilter;
use LibreNMS\Alerting\QueryBuilderFluentParser;
use Toastr;

class DeviceGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $this->authorize('manage', DeviceGroup::class);

        return view('device-group.index', [
            'device_groups' => DeviceGroup::orderBy('name')->withCount('devices')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
//        $this->authorize('create', DeviceGroup::class);

        return view('device-group.create', [
            'device_group' => new DeviceGroup(),
            'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        $this->authorize('create', DeviceGroup::class);

        $this->validate($request, [
            'name' => 'required|string|unique:device_groups',
            'type' => 'in:dynamic,static',
            'devices' => 'array',
            'devices.*' => 'integer',
            'rules' => 'json',
        ]);

        $deviceGroup = DeviceGroup::make($request->only(['name', 'desc', 'type']));
        $deviceGroup->rules = json_decode($request->rules);
        $deviceGroup->save();

        Toastr::success(__('Device Group :name created', ['name' => $deviceGroup->name]));

        return redirect(route('device-groups.index'));

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(DeviceGroup $deviceGroup)
    {
        return redirect(url('/devices/group=' . $deviceGroup->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(DeviceGroup $deviceGroup)
    {
//        $this->authorize('edit', $deviceGroup);

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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeviceGroup $deviceGroup)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'type' => 'in:dynamic,static',
            'devices' => 'array',
            'devices.*' => 'integer',
            'rules' => 'json',
        ]);

        $deviceGroup->fill($request->only(['name', 'desc', 'type']));
        $deviceGroup->rules = json_decode($request->rules);

        if ($deviceGroup->type == 'static') {
            // get device_ids from input
            $device_ids = $request->get('devices', []);
        } else {
            // get device ids from query
            $qp = QueryBuilderFluentParser::fromJSON($deviceGroup->rules);
            $device_ids = $qp->toQuery()->distinct()->pluck('devices.device_id');
        }
        $deviceGroup->devices()->sync($device_ids);

        if ($deviceGroup->isDirty()) {
            if ($deviceGroup->save()) {
                Toastr::success(__('Device Group :name updated', ['name' => $deviceGroup->name]));
            } else {
                Toastr::error(__('Failed to save'));
            }
        } else {
            Toastr::info(__('No changes made'));
        }

        return redirect(route('device-groups.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\DeviceGroup $deviceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeviceGroup $deviceGroup)
    {
//        $this->authorize('delete', $deviceGroup);

        $deviceGroup->delete();

        Toastr::success(__('Device Group :name deleted', ['name' => $deviceGroup->name]));

        return redirect(route('device-groups.index'));
    }
}
