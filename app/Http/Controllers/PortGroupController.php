<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\Port;
use App\Models\PortGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, ToastInterface $toast)
    {
        $this->authorize('create', PortGroup::class);

        $this->validate($request, [
            'name' => 'required|string|unique:port_groups',
            'type' => 'nullable|in:dynamic,static',
            'ports' => ['array', PortGroup::portsExistRule()],
            'ports.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        // groups created inline from the device edit page only send a name
        $portGroup = new PortGroup($request->only(['name', 'desc']));
        $portGroup->type = $request->input('type', 'static');
        if ($portGroup->type == 'dynamic') {
            $portGroup->rules = json_decode($request->rules);
        }

        try {
            // a rule failing to query must not leave the group behind
            DB::transaction(function () use ($portGroup, $request): void {
                $portGroup->save();

                if ($portGroup->type == 'static') {
                    $portGroup->ports()->sync($request->input('ports', []));
                }
            });
        } catch (QueryException $e) {
            // the database error holds the query text, log it instead of showing it
            report($e);
            $error = __('Rules resulted in invalid query, check the rules and try again.');

            return $request->expectsJson()
                ? response()->json(['message' => $error, 'errors' => ['rules' => [$error]]], 422)
                : redirect()->back()->withInput()->withErrors(['rules' => $error]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $portGroup->id,
                'text' => $portGroup->name,
                'message' => __('Port Group :name created', ['name' => $portGroup->name]),
            ], 201);
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
            'ports' => ['array', PortGroup::portsExistRule()],
            'ports.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $was_dynamic = $portGroup->type == 'dynamic';
        $portGroup->fill($request->only(['name', 'desc', 'type']));

        $ports_updated = false;
        if ($portGroup->type == 'static') {
            // the form does not list the members of a dynamic group, converting it keeps them
            // and only adds the selected ports, otherwise sync port_ids from input
            $updated = $was_dynamic
                ? $portGroup->ports()->syncWithoutDetaching($request->input('ports', []))
                : $portGroup->ports()->sync($request->input('ports', []));
            // check for attached/detached/updated
            $ports_updated = array_sum(array_map(count(...), $updated)) > 0;
        } else {
            $portGroup->rules = json_decode($request->rules);
        }

        if ($portGroup->isDirty() || $ports_updated) {
            try {
                // membership is resynced when saving, roll the rules back if their query fails
                if (DB::transaction(fn () => $portGroup->save()) || $ports_updated) {
                    $toast->success(__('Port Group :name updated', ['name' => $portGroup->name]));
                } else {
                    $toast->error(__('Failed to save'));

                    return redirect()->back()->withInput();
                }
            } catch (QueryException $e) {
                // the database error holds the query text, log it instead of showing it
                report($e);

                return redirect()->back()->withInput()->withErrors([
                    'rules' => __('Rules resulted in invalid query, check the rules and try again.'),
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
