<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use LibreNMS\Services;
use Toastr;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Service::class, 'service');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function servicesTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'services',
                'services' => Service::orderBy('service_name')->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function errorsTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'errors',
                'services' => Service::isCritical()->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function warningsTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'warnings',
                'services' => Service::isWarning()->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function ignoredTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'ignored',
                'services' => Service::isIgnored()->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function disabledTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'disabled',
                'services' => Service::isDisabled()->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function maintenanceTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'maintenance',
                'services' => Service::isMaintenance()->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function unknownTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.services', [
                'current_tab' => 'unknown',
                'services' => Service::isUnknown()->get(),
                'error_count' => Service::isCritical()->get()->count(),
                'warning_count' => Service::isWarning()->get()->count(),
                'ignored_count' => Service::isIgnored()->get()->count(),
                'disabled_count' => Service::isDisabled()->get()->count(),
                'maintenance_count' => Service::isMaintenance()->get()->count(),
                'unknown_count' => Service::isUnknown()->get()->count(),
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function logTab()
    {
        //$this->authorize('viewAny', PollerCluster::class);

        return view(
            'services.log', [
                'current_tab' => 'log',
                'logs' => \App\Models\Eventlog::get()->where('type', 'service'),
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
            'services.create', [
                'current_tab' => 'create',
                'service' => new Service(),
                'services' => Service::orderBy('service_name')->get(),
                'services_list' => Services::list(),
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
        $request = [
            'service_name' => 'required|string|unique:service',
            'device_id' => 'integer',
            'service_type' => 'string',
            'service_param' => 'nullable|string',
            'service_ip' => 'nullable|string',
            'service_desc' => 'nullable|string',
            'service_changed' => 'integer',
            'service_disabled' => 'integer',
            'service_ignore' => 'integer',
        ];

        $service = Service::make(
            $request->only(
                [
                    'service_name',
                    'device_id',
                    'service_type',
                    'service_param',
                    'service_ip',
                    'service_desc',
                    'service_changed',
                    'service_disabled',
                    'service_ignore',
                ]
            )
        );
        $service->save();

        Toastr::success(__('Service :name created', ['name' => $service->service_name]));

        return redirect()->route('services.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function update(Request $request, Service $service)
    {
        $this->validate(
            $request, [
                'service_name' => [
                    'required',
                    'string',
                    Rule::unique('services')->where(
                        function ($query) use ($service) {
                            $query->where('service_id', '!=', $service->service_id);
                        }
                    ),
                ],
                'service_devices' => 'array',
                'service_devices.*' => 'integer',
                'service_type' => 'string',
                'service_param' => 'nullable|string',
                'service_ip' => 'nullable|string',
                'service_desc' => 'nullable|string',
                'service_changed' => 'integer',
                'service_disabled' => 'integer',
                'service_ignore' => 'integer',
            ]
        );

        $service->fill(
            $request->only(
                [
                    'service_name',
                    'service_type',
                    'service_param',
                    'service_ip',
                    'service_desc',
                    'service_changed',
                    'service_ignore',
                    'service_disabled',
                ]
            )
        );

        if ($service->isDirty()) {
            try {
                if ($service->save()) {
                    Toastr::success(__('Service :name updated', ['name' => $service->service_name]));
                } else {
                    Toastr::error(__('Failed to save'));

                    return redirect()->back()->withInput();
                }
            } catch (Exception $e) {
                return redirect()->back()->withInput()->withErrors([
                    $e->getMessage(),
                ]);
            }
        } else {
            Toastr::info(__('No changes made'));
        }

        return redirect()->route('services.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Service $service
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(Service $service)
    {
        return redirect(url('/services/' . $service->id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Service $service
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(Service $service)
    {
        return view(
            'services.edit', [
                'current_tab' => 'services',
                'service' => $service,
                'services_list' => Services::list(),
            ]
            //
        );
    }
}
