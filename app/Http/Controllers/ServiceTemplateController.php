<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Service;
use App\Models\ServiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Alerting\QueryBuilderFilter;
use LibreNMS\Services;

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
        $this->authorize('viewAny', ServiceTemplate::class);

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
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function store(Request $request, ToastInterface $toast)
    {
        $this->validate(
            $request, [
                'name' => 'required|string|unique:service_templates',
                'groups' => 'array',
                'groups.*' => 'integer',
                'devices' => 'array',
                'devices.*' => 'integer',
                'check' => 'required|string',
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

        $template = new ServiceTemplate(
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
        $toast->success(__('Service Template :name created', ['name' => $template->name]));

        return redirect()->route('services.templates.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  ServiceTemplate  $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(ServiceTemplate $template)
    {
        return redirect(url('/services/templates/' . $template->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ServiceTemplate  $template
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
     * @param  Request  $request
     * @param  ServiceTemplate  $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function update(Request $request, ServiceTemplate $template, ToastInterface $toast)
    {
        $this->validate(
            $request, [
                'name' => [
                    'required',
                    'string',
                    Rule::unique('service_templates')->where(
                        function ($query) use ($template): void {
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
            $updated = $template->devices()->sync($request->input('devices', []));
            // check for attached/detached/updated
            $devices_updated = array_sum(array_map(count(...), $updated)) > 0;
        } elseif ($template->type == 'dynamic') {
            $template->rules = json_decode($request->rules);
        }

        // sync device_group_ids from input
        $updated = $template->groups()->sync($request->input('groups', []));
        // check for attached/detached/updated
        $device_groups_updated = array_sum(array_map(count(...), $updated)) > 0;

        if ($template->isDirty() || $devices_updated || $device_groups_updated) {
            try {
                if ($template->save() || $devices_updated || $device_groups_updated) {
                    $toast->success(__('Service Template :name updated', ['name' => $template->name]));
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

        return redirect()->route('services.templates.index');
    }

    /**
     * Apply specified Service Template to Device Groups.
     *
     * @param  ServiceTemplate  $template
     */
    private function applyDeviceGroups(ServiceTemplate $template): void
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
    }

    /**
     * Apply specified Service Template to Devices.
     *
     * @param  ServiceTemplate  $template
     */
    private function applyDevices(ServiceTemplate $template): void
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
    }

    /**
     * Apply all Service Templates.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function applyAll()
    {
        $this->authorize('update', ServiceTemplate::class);

        foreach (ServiceTemplate::all() as $template) {
            $this->apply($template);
        }
        $msg = __('All Service Templates have been applied');

        return response($msg, 200);
    }

    /**
     * Apply all Service Templates for a device
     */
    public function applyDeviceAll(int $device_id): void
    {
        foreach (ServiceTemplate::all() as $template) {
            if ($template->type == 'dynamic') {
                $template->updateDevices();
            }
            $this->applyDevice($template, $device_id);
        }
    }

    /**
     * Apply specified Service Template.
     *
     * @param  ServiceTemplate  $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function apply(ServiceTemplate $template)
    {
        $this->authorize('update', ServiceTemplate::class);

        if ($template->type == 'dynamic') {
            $template->updateDevices();
        }
        $this->applyDevices($template);
        $this->applyDeviceGroups($template);

        // remove any remaining services no longer in the correct device group
        foreach (Device::notInServiceTemplate($template->id)->notInDeviceGroup($template->groups->pluck('id'))->pluck('device_id') as $device_id) {
            Service::where('device_id', $device_id)->where('service_template_id', $template->id)->delete();
        }
        $msg = __('All Service Templates have been applied');

        return response($msg, 200);
    }

    /**
     * Apply specified Service Template to a device.
     *
     * @param  ServiceTemplate  $template
     * @param  int  $device_id
     */
    private function applyDevice(ServiceTemplate $template, int $device_id): void
    {
        // Check if the device needs to be added
        foreach (Device::inServiceTemplate($template->id)->where('device_id', $device_id)->get() as $device) {
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

            return; // found
        }

        foreach (DeviceGroup::inServiceTemplate($template->id)->get() as $device_group) {
            foreach (Device::inDeviceGroup($device_group->id)->where('device_id', $device_id)->get() as $device) {
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

                return; // found
            }
        }

        // remove if this template no longer applies
        foreach (Device::notInServiceTemplate($template->id)->notInDeviceGroup($template->groups->pluck('id'))->where('device_id', $device_id)->pluck('device_id') as $device_id) {
            Service::where('device_id', $device_id)->where('service_template_id', $template->id)->delete();
        }
    }

    /**
     * Remove specified Service Template.
     *
     * @param  ServiceTemplate  $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function remove(ServiceTemplate $template)
    {
        $this->authorize('update', ServiceTemplate::class);

        Service::where('service_template_id', $template->id)->delete();

        $msg = __('All Service Templates have been applied');

        return response($msg, 200);
    }

    /**
     * Destroy the specified resource from storage.
     *
     * @param  ServiceTemplate  $template
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function destroy(ServiceTemplate $template)
    {
        Service::where('service_template_id', $template->id)->delete();
        $template->delete();

        $msg = __('Service Template :name deleted, Services removed', ['name' => htmlentities($template->name)]);

        return response($msg, 200);
    }
}
