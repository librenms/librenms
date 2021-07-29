<?php

namespace App\Http\Controllers;

use App\Models\PortGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Toastr;

class PortGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('port-group.index', [
            'port_groups' => PortGroup::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('port-group.create', [
            'port_group' => new PortGroup(),
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
            'name' => 'required|string|unique:port_groups',
        ]);

        $portGroup = PortGroup::make($request->only(['name', 'desc']));
        $portGroup->save();

        Toastr::success(__('Port Group :name created', ['name' => $portGroup->name]));

        return redirect()->route('port-groups.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PortGroup $portGroup
     * @return \Illuminate\View\View
     */
    public function edit(PortGroup $portGroup)
    {
        return view('port-group.edit', [
            'port_group' => $portGroup,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PortGroup $portGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PortGroup $portGroup)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('port_groups', 'name')->where(function ($query) use ($portGroup) {
                    $query->where('id', '!=', $portGroup->id);
                }),
            ],
            'desc' => 'string|max:255',
        ]);

        $portGroup->fill($request->only(['name', 'desc']));

        if ($portGroup->save()) {
            Toastr::success(__('Port Group :name updated', ['name' => $portGroup->name]));
        } else {
            Toastr::error(__('Failed to save'));

            return redirect()->back()->withInput();
        }

        return redirect()->route('port-groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PortGroup $portGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(PortGroup $portGroup)
    {
        $portGroup->delete();

        $msg = __('Port Group :name deleted', ['name' => $portGroup->name]);

        return response($msg, 200);
    }
}
