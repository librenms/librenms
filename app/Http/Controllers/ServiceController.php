<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
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
                'services' => Service::orderBy('name')->get(),
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
                'services' => Service::orderBy('name')->get(),
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
            'service.edit', [
                'service' => $service,
                'filters' => json_encode(new QueryBuilderFilter('group')),
                'services' => Services::list(),
            ]
            //
        );
    }
}
