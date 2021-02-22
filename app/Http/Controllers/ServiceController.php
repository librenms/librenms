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
    public function index()
    {
        //$this->authorize('manage', Service::class);

        return view(
            'services.index', [
                'services' => Service::orderBy('service_name')->get(),
                'services_list' => Services::list(),
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
            }catch (Exception $e) {
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
                'service' => $service,
                'services_list' => Services::list(),
            ]
            //
        );
    }
}
