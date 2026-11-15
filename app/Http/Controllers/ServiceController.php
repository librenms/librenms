<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Services;

class ServiceController extends Controller
{
    private const array STATES = ['ok' => 0, 'warning' => 1, 'critical' => 2];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Service::class);

        $request->validate([
            'state' => ['nullable', Rule::in(['all', ...array_keys(self::STATES)])],
        ]);
        $state = $request->input('state', 'all');

        $services = Service::hasAccess($request->user())
            ->with('device')
            ->when(isset(self::STATES[$state]), fn ($query) => $query->isActive()->where('service_status', self::STATES[$state]))
            ->orderBy('service_type')
            ->orderBy('service_name')
            ->get()
            ->groupBy('device_id')
            ->sortBy(fn ($services) => $services->first()->device?->hostname);

        return view('services.index', [
            'state' => $state,
            'states' => collect(['all' => __('All'), 'ok' => __('Ok'), 'warning' => __('Warning'), 'critical' => __('Critical')])
                ->map(fn ($text, $option) => ['text' => $text, 'link' => route('services.index', ['state' => $option])])
                ->all(),
            'services' => $services,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Service::class);

        $validated = $request->validate([
            'device_id' => 'required|integer|exists:devices,device_id',
            'service_type' => ['required', Rule::in(Services::list())],
            ...$this->rules(),
        ]);

        $device = Device::findOrFail($validated['device_id']);
        $this->authorize('view', $device);

        $service = Services::addService(
            $device->device_id,
            $validated['service_type'],
            strip_tags($validated['service_desc'] ?? ''),
            $validated['service_ip'] ?? '',
            $validated['service_param'] ?? '',
            (int) ($validated['service_ignore'] ?? false),
            (int) ($validated['service_disabled'] ?? false),
            0,
            strip_tags($validated['service_name'] ?? ''),
        );

        return response()->json([
            'message' => __('Service :name added', ['name' => $service->service_name ?: $service->service_type]),
        ]);
    }

    public function show(Service $service): JsonResponse
    {
        $this->authorize('view', $service);

        return response()->json($service->only([
            'service_id',
            'device_id',
            'service_name',
            'service_type',
            'service_desc',
            'service_ip',
            'service_param',
            'service_ignore',
            'service_disabled',
        ]));
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $this->authorize('update', $service);

        $validated = $request->validate($this->rules());
        foreach (array_intersect_key($validated, ['service_name' => 0, 'service_desc' => 0]) as $field => $value) {
            $validated[$field] = strip_tags((string) $value); // legacy pages display these unescaped
        }

        $service->update($validated);

        return response()->json([
            'message' => __('Service :name updated', ['name' => $service->service_name ?: $service->service_type]),
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return response()->json([
            'message' => __('Service :name deleted', ['name' => $service->service_name ?: $service->service_type]),
        ]);
    }

    /**
     * Rules for the fields that can be set when creating and updating a service.
     *
     * @return array<string, string>
     */
    private function rules(): array
    {
        return [
            'service_name' => 'nullable|string|max:255',
            'service_desc' => 'nullable|string',
            'service_ip' => 'nullable|string|max:255',
            'service_param' => 'nullable|string',
            'service_ignore' => 'sometimes|boolean',
            'service_disabled' => 'sometimes|boolean',
        ];
    }
}
