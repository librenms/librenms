<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\ServiceRequest;
use App\Models\Device;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Services;
use LibreNMS\Util\Url;

class ServiceController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Service::class);

        $labels = [
            'all' => __('All'),
            'ok' => __('Ok'),
            'warning' => __('Warning'),
            'critical' => __('Critical'),
            'ignored' => __('Ignored'),
            'disabled' => __('Disabled'),
        ];
        $validated = $request->validate([
            'state' => ['nullable', 'string', Rule::in(array_keys($labels))],
        ]);
        $state = $validated['state'] ?? 'all';
        $filters = $state === 'all' ? [] : ['state' => $state];

        $devices = Device::query()
            ->withWhereHas('services', function ($query) use ($request, $state): void {
                $query->hasAccess($request->user())->ordered();

                match ($state) {
                    'ok' => $query->isOk(),
                    'warning' => $query->isWarning(),
                    'critical' => $query->isCritical(),
                    'ignored' => $query->isIgnored(),
                    'disabled' => $query->isDisabled(),
                    default => null,
                };
            })
            ->orderBy('hostname')
            ->orderBy('device_id')
            ->paginate(25)
            ->appends($filters);

        if ($devices->currentPage() > $devices->lastPage()) {
            $request->session()->keep('toasts');

            return redirect()->to($devices->url($devices->lastPage()));
        }

        $states = [];
        foreach ($labels as $option => $text) {
            $states[$option] = [
                'text' => $text,
                'link' => route('services.index', $option === 'all' ? [] : ['state' => $option]),
            ];
        }

        return view('services.index', compact('devices', 'state', 'states'));
    }

    public function legacy(string $legacy): RedirectResponse
    {
        $options = Url::parseLegacyPath($legacy);
        $state = match (true) {
            $options->get('disabled') === '1' => 'disabled',
            $options->get('ignore') === '1' => 'ignored',
            default => $options->get('state'),
        };

        return redirect()->route('services.index', $state ? ['state' => $state] : [], 301);
    }

    public function store(ServiceRequest $request, ToastInterface $toast): JsonResponse
    {
        $device = Device::findOrFail($request->validated('device_id'));
        $this->authorize('view', $device);

        $service = Services::createForDevice($device, $request->attributesForService());

        return $this->saved($toast, __('Service :name added', ['name' => $service->displayName()]));
    }

    public function show(Service $service): JsonResponse
    {
        $this->authorize('view', $service);

        return $this->serviceData($service);
    }

    public function edit(Service $service): JsonResponse
    {
        $this->authorize('update', $service);

        return $this->serviceData($service);
    }

    public function update(ServiceRequest $request, Service $service, ToastInterface $toast): JsonResponse
    {
        $service->update($request->attributesForService());

        return $this->saved($toast, __('Service :name updated', ['name' => $service->displayName()]));
    }

    public function destroy(Service $service, ToastInterface $toast): JsonResponse
    {
        $this->authorize('delete', $service);
        $service->delete();

        return $this->saved($toast, __('Service :name deleted', ['name' => $service->displayName()]));
    }

    private function serviceData(Service $service): JsonResponse
    {
        return response()->json([
            ...$service->only([
                'service_id',
                'device_id',
                'service_name',
                'service_type',
                'service_desc',
                'service_ip',
                'service_param',
            ]),
            'service_ignore' => (bool) $service->service_ignore,
            'service_disabled' => (bool) $service->service_disabled,
        ]);
    }

    private function saved(ToastInterface $toast, string $message): JsonResponse
    {
        $toast->success(e($message));

        return response()->json(['message' => $message]);
    }
}
