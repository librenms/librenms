<?php

namespace App\Http\Controllers;

use App\Models\DeviceGroup;
use App\Models\ServiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Services;
use Toastr;

class ServiceTemplateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ServiceTemplate::class, 'service_template');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('service-template.index', [
            'service_templates' => ServiceTemplate::orderBy('name')->get(),
            'device_groups' => DeviceGroup::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        return view('service-template.create', [
            'service_template' => new ServiceTemplate(),
            'device_groups' => DeviceGroup::orderBy('name')->get(),
            'services' => Services::list(),
            //'filters' => json_encode(new QueryBuilderFilter('group')),
            //FIXME do i need the above?
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:service_templates',
            'device_group_id' => 'integer',
            'type' => 'string',
            'param' => 'string',
            'ip' => 'string',
            'desc' => 'string',
            'changed' => 'integer',
            'disabled' => 'integer',
            'ignore' => 'integer',
        ]);

        $serviceTemplate = ServiceTemplate::make($request->only([
            'name',
            'device_group_id',
            'type',
            'param',
            'ip',
            'desc',
            'changed',
            'disabled',
            'ignore',
        ]));
        $serviceTemplate->save();

        Toastr::success(__('Service Template :name created', ['name' => $serviceTemplate->name]));

        return redirect()->route('services.templates.index');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(ServiceTemplate $serviceTemplate)
    {
        return redirect(url('/services/templates/=' . $serviceTemplate->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(ServiceTemplate $serviceTemplate)
    {
        return view('service-template.edit', [
            'service_template' => $serviceTemplate,
            //'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function update(Request $request, ServiceTemplate $serviceTemplate)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                Rule::unique('service_templates')->where(function ($query) use ($serviceTemplate) {
                    $query->where('id', '!=', $serviceTemplate->id);
                }),
            ],
            'device_group_id' => 'integer',
            'type' => 'string',
            'param' => 'string',
            'ip' => 'string',
            'desc' => 'string',
            'changed' => 'integer',
            'disabled' => 'integer',
            'ignore' => 'integer',
        ]);

        $serviceTemplate->fill($request->only(['name', 'device_group_id', 'type', 'param', 'ip', 'desc', 'changed', 'ignore', 'disable']));

        if ($serviceTemplate->isDirty() || $devices_updated) {
            try {
                if ($serviceTemplate->save() || $devices_updated) {
                    Toastr::success(__('Service Template :name updated', ['name' => $serviceTemplate->name]));
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

        return redirect()->route('services.templates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function destroy(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->delete();

        Toastr::success(__('Service Template :name deleted', ['name' => $serviceTemplate->name]));

        return redirect()->route('services.templates.index');
    }
}
