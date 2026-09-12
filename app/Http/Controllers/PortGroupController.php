<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\Port;
use App\Models\PortGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Alerting\QueryBuilderFilter;

class PortGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PortGroup::class);

        return view('port-group.index', [
            'port_groups' => PortGroup::hasAccess($request->user())
                ->orderBy('name')->withCount('ports')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', PortGroup::class);

        return view('port-group.create', [
            'port_group' => new PortGroup(),
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
        $this->authorize('create', PortGroup::class);

        $this->validate($request, [
            'name' => 'required|string|unique:port_groups',
            'type' => 'required|in:dynamic,static',
            'ports' => 'array|required_if:type,static',
            'ports.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $portGroup = new PortGroup($request->only(['name', 'desc', 'type']));
        $portGroup->rules = json_decode($request->rules);
        $portGroup->save();

        if ($request->type == 'static') {
            $portGroup->ports()->sync($request->ports);
        }

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
        $this->authorize('update', $portGroup);

        return view('port-group.edit', [
            'port_group' => $portGroup,
            'filters' => json_encode(new QueryBuilderFilter('group')),
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
        $this->authorize('update', $portGroup);

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
            'type' => 'required|in:dynamic,static',
            'ports' => 'array|required_if:type,static',
            'ports.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $portGroup->fill($request->only(['name', 'desc', 'type']));

        $ports_updated = false;
        if ($portGroup->type == 'static') {
            // sync port_ids from input
            $updated = $portGroup->ports()->sync($request->input('ports', []));
            // check for attached/detached/updated
            $ports_updated = array_sum(array_map(count(...), $updated)) > 0;
        } else {
            $portGroup->rules = json_decode($request->rules);
        }

        if ($portGroup->isDirty() || $ports_updated) {
            try {
                if ($portGroup->save() || $ports_updated) {
                    $toast->success(__('Port Group :name updated', ['name' => $portGroup->name]));
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
        $this->authorize('delete', $portGroup);

        $portGroup->delete();

        $msg = __('Port Group :name deleted', ['name' => htmlentities($portGroup->name)]);

        return response($msg, 200);
    }

    /**
     * Show graph of all ports in a group
     */
    public function graph(Request $request, PortGroup $group): View
    {
        $this->authorize('view', $group);

        return view('port-group.graph', [
            'group' => $group,
            'ports' => Port::hasAccess($request->user())->inPortGroup($group->id)->with('device')->withCount('macAccounting')->get(),
        ]);
    }
}
