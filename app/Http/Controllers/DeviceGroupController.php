<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroup;
use Illuminate\Http\Request;
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
            'device_groups' => DeviceGroup::orderBy('name')->get(),
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

        return view('device-group.create');
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
            'name' => 'required',
            'type' => 'in:dynamic,static'
        ]);

        $deviceGroup = DeviceGroup::create($request->all());
        Toastr::success(__('Device Group :name created', ['name' => $deviceGroup->name]));

        return redirect(route('device-group.index'));

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

        return view('device-group.edit', [
            'device_group' => $deviceGroup,
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
            'name' => 'required',
            'type' => 'in:dynamic,static'
        ]);

        $deviceGroup->fill($request->all());

        if ($deviceGroup->isDirty()) {
            if ($deviceGroup->save()) {
                Toastr::success(__('Device Group :name updated', ['name' => $deviceGroup->name]));
            } else {
                Toastr::error(__('Failed to save'));
            }
        } else {
            Toastr::info(__('No changes made'));
        }

        return redirect(route('device-group.index'));
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

        return redirect(route('device-group.index'));
    }
}
