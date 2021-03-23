<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Service;
use App\Models\ServiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Alerting\QueryBuilderFilter;
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
        //$this->authorize('manage', ServiceTemplate::class);

        return view(
            'service-template.index', [
                'service_templates' => ServiceTemplate::orderBy('name')->withCount('devices')->withCount('groups')->get(),
                'groups' => DeviceGroup::orderBy('name')->has('serviceTemplates')->get(),
                'devices' => Device::orderBy('hostname')->has('serviceTemplates')->get(),
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        return view(
            'service-template.create', [
                'template' => new ServiceTemplate(),
                'service_templates' => ServiceTemplate::orderBy('name')->get(),
                'services' => Services::list(),
                'filters' => json_encode(new QueryBuilderFilter('group')),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        $this->validate(
            $request, [
                'name' => 'required|string|unique:service_templates',
                'groups' => 'array',
                'groups.*' => 'integer',
                'devices' => 'array',
                'devices.*' => 'integer',
                'check' => 'string',
                'type' => 'required|in:dynamic,static',
                'rules' => 'json|required_if:type,dynamic',
                'param' => 'nullable|string',
                'ip' => 'nullable|string',
                'desc' => 'nullable|string',
                'changed' => 'integer',
                'disabled' => 'integer',
                'ignore' => 'integer',
            ]
        );

        $template = ServiceTemplate::make(
            $request->only(
                [
                    'name',
                    'check',
                    'type',
                    'rules',
                    'param',
                    'ip',
                    'desc',
                    'changed',
                    'disabled',
                    'ignore',
                ]
            )
        );
        $template->rules = json_decode($request->rules);
        $template->save();

        if ($request->type == 'static') {
            $template->devices()->sync($request->devices);
        }

        $template->groups()->sync($request->groups);
        Toastr::success(__('Service Template :name created', ['name' => $template->name]));

        return redirect()->route('services.templates.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(ServiceTemplate $template)
    {
        return redirect(url('/services/templates/' . $template->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(ServiceTemplate $template)
    {
        return view(
            'service-template.edit', [
                'template' => $template,
                'filters' => json_encode(new QueryBuilderFilter('group')),
                'services' => Services::list(),
            ]
            //
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function update(Request $request, ServiceTemplate $template)
    {
        $this->validate(
            $request, [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('service_templates')->where(
                        function ($query) use ($template) {
                            $query->where('id', '!=', $template->id);
                        }
                    ),
                ],
                'type' => 'required|in:dynamic,static',
                'rules' => 'json|required_if:type,dynamic',
                'devices' => 'array',
                'devices.*' => 'integer',
                'groups' => 'array',
                'groups.*' => 'integer',
                'check' => 'string',
                'param' => 'nullable|string',
                'ip' => 'nullable|string',
                'desc' => 'nullable|string',
                'changed' => 'integer',
                'disabled' => 'integer',
                'ignore' => 'integer',
            ]
        );

        $template->fill(
            $request->only(
                [
                    'name',
                    'check',
                    'type',
                    'rules',
                    'param',
                    'ip',
                    'desc',
                    'changed',
                    'ignore',
                    'disabled',
                ]
            )
        );

        $devices_updated = false;
        if ($template->type == 'static') {
            // sync device_ids from input
            $updated = $template->devices()->sync($request->get('devices', []));
            // check for attached/detached/updated
            $devices_updated = array_sum(array_map(function ($device_ids) {
                return count($device_ids);
            }, $updated)) > 0;
        } elseif ($template->type == 'dynamic') {
            $template->rules = json_decode($request->rules);
        } elseif ($template->type == 'groups') {
            // sync device_group_ids from input
            $updated = $template->groups()->sync($request->get('groups', []));
            // check for attached/detached/updated
            $device_groups_updated = array_sum(array_map(function ($device_group_ids) {
                return count($device_group_ids);
            }, $updated)) > 0;
        }

        if ($template->isDirty() || $devices_updated || isset($device_groups_updated)) {
            try {
                if ($template->save() || $devices_updated || isset($device_groups_updated)) {
                    Toastr::success(__('Service Template :name updated', ['name' => $template->name]));
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
     * Apply specified Service Template to Device Groups.
     *
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function applyDeviceGroups(ServiceTemplate $template)
    {
        foreach (DeviceGroup::inServiceTemplate($template->id)->get() as $device_group) {
            foreach (Device::inDeviceGroup($device_group->id)->get() as $device) {
                $device->services()->updateOrCreate(
                    [
                        'service_template_id' => $template->id,
                    ],
                    [
                        'service_name' => $template->name,
                        'service_type' => $template->check,
                        'service_template_id' => $template->id,
                        'service_param' => $template->param,
                        'service_ip' => $template->ip,
                        'service_desc' => $template->desc,
                        'service_disabled' => $template->disabled,
                        'service_ignore' => $template->ignore,
                    ]
                );
            }
        }
        $msg = __('Services for Template :name have been updated', ['name' => $template->name]);

        return response($msg, 200);
    }

    /**
     * Apply specified Service Template to Devices.
     *
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function applyDevices(ServiceTemplate $template)
    {
        foreach (Device::inServiceTemplate($template->id)->get() as $device) {
            $device->services()->updateOrCreate(
                [
                    'service_template_id' => $template->id,
                ],
                [
                    'service_name' => $template->name,
                    'service_type' => $template->check,
                    'service_template_id' => $template->id,
                    'service_param' => $template->param,
                    'service_ip' => $template->ip,
                    'service_desc' => $template->desc,
                    'service_disabled' => $template->disabled,
                    'service_ignore' => $template->ignore,
                ]
            );
        }
        $msg = __('Services for Template :name have been updated', ['name' => $template->name]);

        return response($msg, 200);
    }

    /**
     * Apply all Service Templates.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function applyAll()
    {
        foreach (ServiceTemplate::all() as $template) {
            $this->apply($template);
        }
        $msg = __('All Service Templates have been applied');

        return response($msg, 200);
    }

    /**
     * Apply specified Service Template.
     *
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function apply(ServiceTemplate $template)
    {
        ServiceTemplateController::applyDevices($template);
        ServiceTemplateController::applyDeviceGroups($template);

        // remove any remaining services no longer in the correct device group
        foreach (Device::notInServiceTemplate($template->id)->notInDeviceGroup($template->groups)->get() as $device) {
            Service::where('device_id', $device->device_id)->where('service_template_id', $template->id)->delete();
        }
        $msg = __('All Service Templates have been applied');

        return response($msg, 200);
    }

    /**
     * Remove specified Service Template.
     *
     * @param  \App\Models\ServiceTemplate $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function remove(ServiceTemplate $template)
    {
        Service::where('service_template_id', $template->id)->delete();

        $msg = __('All Service Templates have been applied');

        return response($msg, 200);
    }

    /**
     * Destroy the specified resource from storage.
     *
     * @param  \App\Models\ServiceTemplate $template
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
