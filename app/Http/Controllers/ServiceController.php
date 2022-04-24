<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Services;
use LibreNMS\Services\CheckParameter;

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
        $services = Services::list();
        $param_rules = $this->buildParamRules($request->get('service_type'), $services);
        unset($param_rules['service_param.--hostname']);

        $validated = $this->validate($request, [
                'device_id' => 'required|int|exists:devices,device_id',
                'service_type' => [
                    'required',
                    Rule::in($services),
                ],
                'service_ip' => 'nullable|ip_or_hostname',
                'service_desc' => 'nullable|string',
                'service_param' => 'nullable|array',
                'service_param.*' => 'string',
                'service_ignore' => 'boolean',
                'service_disabled' => 'boolean',
                'service_name' => 'nullable|string',
                'service_template_id' => 'nullable|int|exists:App\Models\ServiceTemplate,id',
            ] + $param_rules);

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

    /**
     * @param  string  $type
     * @param  string[]  $services
     * @return array
     */
    private function buildParamRules(string $type, array $services): array
    {
        $parameter_rules = [];

        // don't try to load a check that isn't valid
        if (! in_array($type, $services)) {
            return $parameter_rules;
        }

        $check = Services::makeCheck(new Service(['service_type' => $type]));

        $parameters = $check->availableParameters();
        $keyed = collect($parameters)->keyBy('short');
        foreach ($parameters as $parameter) {
            $rules = [];
            $param = $parameter->param ?: $parameter->short;

            if ($parameter->required) {
                $rules[] = 'required';
            } elseif ($parameter->inclusive_group) {
                $rules[] = 'required_with:' . implode(',', $keyed->only($parameter->inclusive_group)->map(function (CheckParameter $param) {
                    return 'service_param.' . $param->param ?: $param->short;
                    })->all());
            }

            if ($parameter->exclusive_group) {
                $rules[] = 'exclude_with:' . implode(',', $keyed->only($parameter->inclusive_group)->map(function (CheckParameter $param) {
                        return 'service_param.' . $param->param ?: $param->short;
                    })->all());
            }

            if ($parameter->value == 'INTEGER') {
                $rules[] = 'integer';
            } elseif ($parameter->value == 'ADDRESS') {
                $rules[] = 'ip_or_hostname';
            } elseif ($parameter->value == 'DOUBLE') {
                $rules[] = 'numeric';
            }

            $parameter_rules["service_param.$param"] = $rules;
        }

        return $parameter_rules;
    }
}
