<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function store(Request $request, ToastInterface $toast)
    {
        $this->authorize('create', Service::class);

        $this->validate($request, [
            'service_name' => 'required|string|unique:service',
            'device_id' => 'integer',
            'service_type' => 'string',
            'service_param' => 'nullable|string',
            'service_ip' => 'nullable|string',
            'service_desc' => 'nullable|string',
            'service_changed' => 'integer',
            'service_disabled' => 'integer',
            'service_ignore' => 'integer',
        ]);

        $service = new Service(
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

        $toast->success(__('Service :name created', ['name' => $service->service_name]));

        return redirect()->route('services.templates.index');
    }

    /**
     * Display the specified service.
     *
     * @param  Service  $service
     * @return JsonResponse
     */
    public function show(Service $service): JsonResponse
    {
        $this->authorize('view', $service);

        return response()->json([
            'stype' => $service->service_type,
            'ip' => $service->service_ip,
            'desc' => $service->service_desc,
            'param' => $service->service_param,
            'ignore' => $service->service_ignore,
            'disabled' => $service->service_disabled,
            'template_id' => $service->service_template_id,
            'name' => $service->service_name,
        ]);
    }

    /**
     * Remove the specified service from storage.
     *
     * @param  Service  $service
     * @return JsonResponse
     */
    public function destroy(Service $service): JsonResponse
    {
        $this->authorize('delete', $service);

        $id = $service->service_id;

        if ($service->delete()) {
            return response()->json([
                'status' => 0,
                'message' => "Service: <i>$id, has been deleted.</i>",
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => "Service: <i>$id, has NOT been deleted.</i>",
        ]);
    }
}
