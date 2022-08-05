<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Service;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\CheckStatus;
use LibreNMS\Services;
use LibreNMS\Services\CheckParameter;

class ServicesController extends Controller
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
    public function index(Request $request)
    {
        $state = $request->input('state', 'all');
        $disabled = $request->input('disabled', false);
        $ignored = $request->input('ignored', false);

        $devices = Device::with(['services' => $this->filterServices($state, $disabled, $ignored)])
            ->whereHas('services', $this->filterServices($state, $disabled, $ignored))
            ->get();

        $view_menu = [
            'view' => [
                ['key' => 'basic', 'name' => trans('service.view_basic')],
                ['key' => 'detailed', 'name' => trans('service.view_detailed'), 'default' => true],
                ['key' => 'graphs', 'name' => trans('service.view_graphs')],
            ],
        ];

        $state_menu = [
            'state' => [
                ['key' => 'all', 'name' => trans('service.state_all'), 'default' => true],
                ['key' => 'ok', 'name' => trans('service.state_ok')],
                ['key' => 'warning', 'name' => trans('service.state_warning')],
                ['key' => 'critical', 'name' => trans('service.state_critical')],
            ],
        ];

        return view('service.index', [
            'devices' => $devices,
            'view_menu' => $view_menu,
            'state_menu' => $state_menu,
            'view' => $request->input('view'),
        ]);
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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $service = $this->validateNewService($request);
        $service->save();

        return response()->json($service);
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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $services = Services::list();
        $param_rules = $this->buildParamRules($request->get('service_type'), $services);
        unset($param_rules['service_param.--hostname']);

        $validated = $this->validate($request, [
            'device_id' => 'int|exists:App\Models\Device,device_id',
            'service_ip' => 'nullable|ip_or_hostname',
            'service_type' => ['nullable', Rule::in($services)],
            'service_desc' => 'nullable|string',
            'service_param' => 'nullable|array',
            'service_param.*' => 'string',
            'service_ignore' => 'boolean',
            'service_disabled' => 'boolean',
            'service_name' => 'nullable|string',
            'service_template_id' => 'nullable|int|exists:App\Models\ServiceTemplate,id',
        ] + $param_rules);

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
                'service_id' => $service->service_id,
                'message' => __('service.deleted', ['service' => $service->service_name . " ($service->service_id)"]),
            ]);
        }

        return response()->json([
            'status' => 0,
            'service_id' => $service->service_id,
            'message' => __('service.not_deleted', ['service' => $service->service_name . " ($service->service_id)"]),
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

        $parameters = $check->availableParameters()->keyBy('short');

        foreach ($parameters as $parameter) {
            $rules = [];
            $param = $parameter->param ?: $parameter->short;

            if ($parameter->required) {
                $rules[] = 'required';
            } elseif ($parameter->inclusive_group) {
                $rules[] = 'required_with:' . implode(',', $parameters->only($parameter->inclusive_group)->map(function (CheckParameter $param) {
                    return 'service_param.' . ($param->param ?: $param->short);
                })->all());
            }

            if ($parameter->exclusive_group) {
                $rules[] = 'exclude_with:' . implode(',', $parameters->only($parameter->inclusive_group)->map(function (CheckParameter $param) {
                    return 'service_param.' . ($param->param ?: $param->short);
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

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function test(Request $request): JsonResponse
    {
        $service = $this->validateNewService($request);
        $response = app(\LibreNMS\Modules\Services::class)->checkService($service);

        $message = $response->message;

        // prepend the CLI for troubleshooting if the check fails
        if ($response->result == 2) {
            $message = $response->commandLine . PHP_EOL . PHP_EOL . $message;
        }

        return response()->json([
            'message' => $message,
            'result' => $response->result,
        ]);
    }

    /**
     * @param  string  $state
     * @param  bool  $disabled
     * @param  bool  $ignored
     * @return \Closure
     */
    private function filterServices($state, $disabled, $ignored): Closure
    {
        return function ($query) use ($state, $disabled, $ignored) {
            if ($state !== 'all') {
                $query->where('service_status', CheckStatus::toState($state));
            }

            if ($disabled) {
                $query->where('service_disabled', 1);
            }

            if ($ignored) {
                $query->where('service_ignore', 1);
            }
        };
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Service
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateNewService(Request $request): Service
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

        return new Service($validated);
    }
}
