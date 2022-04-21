<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Services;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Service::class, 'service');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('services.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('service.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validate($request, [
            'device_id' => 'required|int|exists:App\Models\Device',
            'service_type' => [
                'required',
                Rule::in(Services::list()),
            ],
            'service_ip' => 'nullable|ip_or_hostname',
            'service_desc' => 'nullable|string',
            'service_param' => 'nullable|array',
            'service_param.*' => 'string',
            'service_ignore' => 'nullable|in:0,1',
            'service_disabled' => 'nullable|in:0,1',
            'service_name' => 'nullable|string',
            'service_template_id' => 'nullable|int|exists:App\Models\ServiceTemplate',
        ]);

        return response()->json(Service::create($validated));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $this->validate($request, [
            'device_id' => 'int|exists:App\Models\Device,device_id',
            'service_ip' => 'nullable|ip_or_hostname',
            'service_type' => ['nullable', Rule::in(Services::list())],
            'service_desc' => 'string',
            'service_param' => 'nullable|array',
            'service_param.*' => 'string',
            'service_ignore' => 'boolean',
            'service_disabled' => 'boolean',
            'service_name' => 'string',
            'service_template_id' => 'nullable|int|exists:App\Models\ServiceTemplate,id',
        ]);

        $service->fill($validated);
        $service->save();

        return response()->json($service);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Service $service): JsonResponse
    {
        if ($service->delete()) {
            return response()->json([
                'status' => 1,
                'message' => 'Service: ' . $service->service_id . ', has been deleted.',
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Service: ' . $service->service_id . ', has NOT been deleted.',
        ]);
    }
}
