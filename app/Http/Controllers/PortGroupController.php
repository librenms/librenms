<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\PortGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'port_groups' => PortGroup::orderBy('name')->withCount('ports')->get(),
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
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, ToastInterface $toast)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:port_groups',
        ]);

        $portGroup = new PortGroup($request->only(['name', 'desc']));
        $portGroup->save();

        $toast->success(__('Port Group :name created', ['name' => $portGroup->name]));

        return redirect()->route('port-groups.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  PortGroup  $portGroup
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
     * @param  Request  $request
     * @param  PortGroup  $portGroup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PortGroup $portGroup, ToastInterface $toast)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('port_groups', 'name')->where(function ($query) use ($portGroup): void {
                    $query->where('id', '!=', $portGroup->id);
                }),
            ],
            'desc' => 'string|max:255',
        ]);

        $portGroup->fill($request->only(['name', 'desc']));

        if ($portGroup->save()) {
            $toast->success(__('Port Group :name updated', ['name' => $portGroup->name]));
        } else {
            $toast->error(__('Failed to save'));

            return redirect()->back()->withInput();
        }

        return redirect()->route('port-groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PortGroup  $portGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(PortGroup $portGroup)
    {
        $portGroup->delete();

        $msg = __('Port Group :name deleted', ['name' => htmlentities($portGroup->name)]);

        return response($msg, 200);
    }
}
