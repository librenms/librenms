<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Service;
use App\Models\ServiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Services;
use Toastr;

class ServiceTemplateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ServiceTemplate::class, 'template');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('service-template.index', [
            'device_groups' => DeviceGroup::with('serviceTemplates')->orderBy('name')->get(),
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
            'param' => 'nullable|string',
            'ip' => 'nullable|string',
            'desc' => 'nullable|string',
            'changed' => 'integer',
            'disabled' => 'integer',
            'ignore' => 'integer',
        ]);

        $template = ServiceTemplate::make($request->only([
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
        $template->save();

        Toastr::success(__('Service Template :name created', ['name' => $template->name]));

        return redirect()->route('services.templates.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param App\Models\ServiceTemplate $template
     * @param App\Models\Device $device
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function storeservice(Request $request, ServiceTemplate $template, $device)
    {
        $request = [
            'service_name' => $template->name,
            'device_id' => $device,
            'service_type' => $template->type,
            'service_param' => $template->param,
            'service_ip' => $template->ip,
            'service_desc' => $template->desc,
            'service_changed' => $template->changed,
            'service_disabled' => $template->disabled,
            'service_ignore' => $template->ignore,
        ];

        $service = Service::make($request->only([
            'service_name',
            'device_id',
            'service_type',
            'service_param',
            'service_ip',
            'service_desc',
            'service_changed',
            'service_disabled',
            'service_ignore',
        ]));
        if ($service->save()) {
            log_event("Service: {$template->name} created from Service Template ID: {$template->id}", $device, 'service', 2);

            return true;
        } else {
            log_event("Service: {$template->name} creation FAILED from Service Template ID: {$template->id}", $device, 'service', 2);

            return false;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(ServiceTemplate $template)
    {
        return redirect(url('/services/templates/' . $template->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(ServiceTemplate $template)
    {
        return view('service-template.edit', [
            'service_template' => $template,
            'device_groups' => DeviceGroup::orderBy('name')->get(),
            'services' => Services::list(),
            //'filters' => json_encode(new QueryBuilderFilter('group')),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function update(Request $request, ServiceTemplate $template)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                Rule::unique('service_templates')->where(function ($query) use ($template) {
                    $query->where('id', '!=', $template->id);
                }),
            ],
            'device_group_id' => 'integer',
            'type' => 'string',
            'param' => 'nullable|string',
            'ip' => 'nullable|string',
            'desc' => 'nullable|string',
            'changed' => 'integer',
            'disabled' => 'integer',
            'ignore' => 'integer',
        ]);

        $template->fill($request->only([
            'name',
            'device_group_id',
            'type',
            'param',
            'ip',
            'desc',
            'changed',
            'ignore',
            'disable',
        ]));

        if ($template->isDirty()) {
            if ($template->save()) {
                Toastr::success(__('Service Template :name updated', ['name' => $template->name]));
            } else {
                Toastr::error(__('Failed to save'));

                return redirect()->back()->withInput();
            }
        } else {
            Toastr::info(__('No changes made'));
        }

        return redirect()->route('services.templates.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function apply(ServiceTemplate $template)
    {
        foreach (Device::inDeviceGroup($template->device_group_id)->pluck('device_id') as $device) {
            foreach (Service::where('service_template_id', $template->id)->where('device_id', $device)->pluck('service_id') as $service) {
                $request = [
                    'service_name' => $template->name,
                    'service_type' => $template->type,
                    'service_param' => $template->param,
                    'service_ip' => $template->ip,
                    'service_desc' => $template->desc,
                    'service_changed' => $template->changed,
                    'service_disabled' => $template->disabled,
                    'service_ignore' => $template->ignore,
                ];

                $template->fill($request->only([
                    'service_name',
                    'service_type',
                    'service_param',
                    'service_ip',
                    'service_desc',
                    'service_changed',
                    'service_ignore',
                    'service_disable',
                ]));

                if ($service->isDirty()) {
                    if ($service->save()) {
                        log_event("Service: {$template->name} updated Service Template ID: {$template->id}", $device, 'service', 2);
                    } else {
                        log_event("Service: {$template->name} update FAILED Service Template ID: {$template->id}", $device, 'service', 2);
                    }
                }
            }
            if (! Service::where('service_template_id', $service_template)->where('device_id', $device)->count()) {
                storeservice($request, $device);
                log_event("Added Service: {$template['name']} from Service Template ID: {$template['id']}", $device, 'service', 2);
            }
        }
        // remove any remaining services for this template that haven't been updated (they are no longer in the correct device group)
        Service::where('service_template_id', $template)->where('service_template_changed', '!=', $services->changed)->delete();
        $msg = __('Services for Template :name have been updates', ['name' => $template->name]);

        return response($msg, 200);
    }

    /**
     * Remove the Services for the specified resource.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function applyall()
    {
        $service_templates = ServiceTemplate::get('id');

        foreach ($service_templates as $service_template) {
            apply($service_template->id);
        }

        return response($msg, 200);
    }

    /**
     * Remove the Services for the specified resource.
     *
     * @param \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function remove(ServiceTemplate $template)
    {
        if (Service::where('service_template_id', $template->id)->delete()) {
            $msg = __('Services for Template :name have been removed', ['name' => $template->name]);
        } else {
            $msg = __('No Services for Template :name were removed', ['name' => $template->name]);
        }

        return response($msg, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function destroy(ServiceTemplate $template)
    {
        Service::where('service_template_id', $template->id)->delete();
        $template->delete();

        $msg = __('Service Template :name deleted, Services removed', ['name' => $template->name]);

        return response($msg, 200);
    }
}
