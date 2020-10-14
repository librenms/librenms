<?php

namespace App\Http\Controllers;

use App\Models\ServiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Alerting\QueryBuilderFilter;
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('manage', ServiceTemplate::class);

        return view('service-template.index', [
            'service_templates' => ServiceTemplate::orderBy('system_template_name')->withCount('device_groups')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('service-template.create', [
            'service_template' => new ServiceTemplate(),
            //'filters' => json_encode(new QueryBuilderFilter('group')),
            //FIXME do i need the above?
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
        $this->validate($request, [
            'service_template_name' => 'required|string|unique:service_templates',
            'device_group_id' => 'integer',
            'service_template_type' => 'required|in:dynamic,static',
            'service_template_param' => 'string',
            'service_template_ip' => 'string',
            'service_template_desc' => 'string',
            'service_template_changed' => 'integer',
            'service_template_disabled' => 'integer',
            'service_template_ignore' => 'integer',
        ]);

        $serviceTemplate = ServiceTemplate::make($request->only(['service_template_name', 'device_group_id', 'service_template_type', 'service_template_param', 'service_template_ip', 'service_template_desc', 'service_template_changed', 'service_template_disabled', 'service_template_ignore']));
        $serviceTemplate->save();

        Toastr::success(__('Service Template :system_template_name created', ['system_template_name' => $serviceTemplate->service_template_name]));

        return redirect()->route('service-templates.index');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceTemplate $serviceTemplate)
    {
        return redirect(url('/services-templates/device_group_id=' . $serviceTemplate->device_group_id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ServiceTemplate $serviceTemplate)
    {
        $this->validate($request, [
            'service_template_name' => [
                'required',
                'string',
                Rule::unique('service_templates')->where(function ($query) use ($serviceTemplate) {
                    $query->where('service_template_id', '!=', $serviceTemplate->service_template_id);
                }),
            ],
            'service_template_type' => 'required|in:dynamic,static',
            'device_group_id' => 'integer',
            'service_template_param' => 'string',
            'service_template_ip' => 'string',
            'service_template_desc' => 'string',
            'service_template_changed' => 'integer',
            'service_template_disabled' => 'integer',
            'service_template_ignore' => 'integer',
        ]);

        $serviceTemplate->fill($request->only(['service_template_name', 'device_group_id', 'service_template_type', 'service_template_param', 'service_template_ip', 'service_template_desc', 'service_template_changed', 'service_template_ignore', 'service_template_disable']));

        if ($serviceTemplate->isDirty() || $devices_updated) {
            try {
                if ($serviceTemplate->save() || $devices_updated) {
                    Toastr::success(__('Service Template :system_template_name updated', ['system_template_name' => $serviceTemplate->system_template_name]));
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

        return redirect()->route('service-templates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->delete();

        Toastr::success(__('Service Template :system_template_name deleted', ['system_template_name' => $serviceTemplate->system_template_name]));

        return redirect()->route('service-templates.index');
    }
}
