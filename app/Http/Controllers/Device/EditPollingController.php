<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\ResolvePollingMethodSecret;
use App\Actions\Device\SetDeviceAvailability;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\SavePollingMethodRequest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\ProbeResult;

class EditPollingController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PollingMethodRegistry $pollingMethods,
        private readonly ResolvePollingMethodSecret $resolveSecret,
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $device->load('pollingMethods.secret');

        $allMethods = collect(PollingMethodType::cases())->map(
            fn (PollingMethodType $type): array => $this->buildMethodData($device, $type)
        );

        return view('device.edit.polling', [
            'device' => $device,
            'allMethods' => $allMethods,
            'tabsConfig' => $this->buildTabsConfig($allMethods),
            'availableSecrets' => Secret::query()
                ->when(auth()->user(), fn ($q, $user) => $q->hasAccess($user))
                ->orderBy('description')
                ->get(['id', 'description', 'secret_type'])
                ->groupBy(fn (Secret $s): string => $s->secret_type->value),
        ]);
    }

    /**
     * Initial state for the polling tabs Alpine component.
     *
     * @param  Collection<int, array<string, mixed>>  $allMethods
     * @return array{initialTab: string, activeMethods: list<string>, methods: array<string, array{configured: bool, enabled: bool, affectsAvailability: bool, lastCheckSuccessful: ?bool}>, allTypes: list<array{type: string, label: string}>}
     */
    private function buildTabsConfig(Collection $allMethods): array
    {
        $configuredMethods = $allMethods->filter(fn (array $m): bool => $m['configured'])->values();
        $snmpConfigured = $configuredMethods->firstWhere('type', PollingMethodType::Snmp->value);
        $defaultTab = ($snmpConfigured && ! empty($snmpConfigured['enabled'])) ? PollingMethodType::Snmp->value : $configuredMethods->first()['type'] ?? '';
        $initialTab = PollingMethodType::tryFrom((string) request('tab'))->value ?? $defaultTab;

        $activeMethods = $configuredMethods->pluck('type');
        if ($initialTab !== '' && ! $activeMethods->contains($initialTab)) {
            $activeMethods->push($initialTab);
        }

        return [
            'initialTab' => $initialTab,
            'activeMethods' => $activeMethods->values()->all(),
            'methods' => $allMethods->mapWithKeys(fn (array $m): array => [$m['type'] => [
                'configured' => (bool) $m['configured'],
                'enabled' => (bool) $m['enabled'],
                'affectsAvailability' => (bool) $m['affects_availability'],
                'lastCheckSuccessful' => $m['last_check_successful'],
            ]])->all(),
            'allTypes' => $allMethods->map(fn (array $m): array => ['type' => $m['type'], 'label' => $m['label']])->values()->all(),
        ];
    }

    /**
     * Form data for a method. Secret values are not included, the form loads them when needed.
     *
     * @return array<string, mixed>
     */
    private function buildMethodData(Device $device, PollingMethodType $type): array
    {
        $method = $this->pollingMethods->get($type);
        $definition = $method->definition();
        /** @var DevicePollingMethod|null $row */
        $row = $device->pollingMethods->firstWhere('method_type', $type);
        $secretType = $method->secretType();
        $secretDefinition = $secretType?->definition();

        return [
            'type' => $type->value,
            'label' => $type->label(),
            'icon' => $definition->icon(),
            'schema_fields' => $secretDefinition?->buildSchemaFields() ?? [],
            'schema_defaults' => $secretDefinition?->schemaDefaults() ?? [],
            'settings_fields' => $definition->settingsFields($method->defaults($device)),
            'settings' => [...array_fill_keys(array_keys($definition->fields()), ''), ...$row->settings ?? []],
            'affects_availability' => $row ? $row->affects_availability : $method->defaultAffectsAvailability(),
            'secret' => $row?->secret ? ['id' => $row->secret->id, 'description' => $row->secret->description] : null,
            'default_secret_description' => $secretType ? Secret::defaultDescription($type, $device->hostname) : null,
            'configured' => $row !== null,
            'enabled' => $row ? $row->enabled : true,
            'last_check_successful' => $row?->last_check_successful,
        ];
    }

    /**
     * @throws AuthorizationException
     */
    public function store(SavePollingMethodRequest $request, Device $device, ToastInterface $toast, SetDeviceAvailability $setDeviceAvailability): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $device);

        $type = $request->pollingType();
        $deviceMethod = new DevicePollingMethod([
            'method_type' => $type,
            'affects_availability' => $this->pollingMethods->get($type)->defaultAffectsAvailability(),
        ]);

        return $this->save($request, $device, $deviceMethod, __('poller.method_added'), $toast, $setDeviceAvailability);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(SavePollingMethodRequest $request, Device $device, ToastInterface $toast, SetDeviceAvailability $setDeviceAvailability): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $device);

        $type = $request->pollingType() ?? abort(404);
        $deviceMethod = $device->pollingMethod($type) ?? abort(404);

        return $this->save($request, $device, $deviceMethod, __('poller.method_updated'), $toast, $setDeviceAvailability);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Device $device, string $methodType, ToastInterface $toast, SetDeviceAvailability $setDeviceAvailability): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $device);

        $type = PollingMethodType::tryFrom($methodType) ?? abort(404);
        $pollingMethod = $device->pollingMethod($type) ?? abort(404);

        $pollingMethod->delete();
        $device->unsetRelation('pollingMethods');

        $setDeviceAvailability->execute($device);

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_removed'),
                'default_secret_description' => $this->pollingMethods->get($type)->secretType() ? Secret::defaultDescription($type, $device->hostname) : null,
            ]);
        }

        $toast->success(__('poller.method_removed'));

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * Apply the request to the method, check it can reach the device (unless forced), then save.
     *
     * @throws AuthorizationException
     */
    private function save(
        SavePollingMethodRequest $request,
        Device $device,
        DevicePollingMethod $deviceMethod,
        string $message,
        ToastInterface $toast,
        SetDeviceAvailability $setDeviceAvailability,
    ): JsonResponse|RedirectResponse {
        $type = $deviceMethod->method_type;
        $method = $this->pollingMethods->get($type);

        if ($request->has('enabled')) {
            $deviceMethod->enabled = $request->boolean('enabled');
        }
        if ($request->has('affects_availability')) {
            $deviceMethod->affects_availability = $request->boolean('affects_availability');
        }
        if ($request->has('settings')) {
            $deviceMethod->settings = $method->definition()->filterOverrides($request->validated('settings', []));
        }

        $secret = $this->resolveSecret->execute($device, $type, $request->validated());
        if ($secret !== null && ! $secret->exists) {
            $this->authorize('create', Secret::class);
        } elseif ($secret?->isDirty()) {
            $this->authorize('update', $secret);
        }
        if ($secret !== null) {
            $deviceMethod->setRelation('secret', $secret);
        }
        $deviceMethod->setRelation('device', $device);

        if ($deviceMethod->enabled) {
            $probeResult = $request->boolean('force_save') ? null : $method->discover($device, $deviceMethod);
            if ($probeResult && ! $probeResult->isSuccess()) {
                return $this->reachabilityFailedResponse($request, $device, $type, $probeResult, $toast);
            }

            // unchecked when force saved, a disabled method keeps its last check status
            $deviceMethod->last_check_successful = $probeResult?->isSuccess();
            $deviceMethod->last_checked_at = $probeResult ? now() : null;
        }

        DB::transaction(function () use ($device, $deviceMethod, $secret): void {
            if ($secret !== null) {
                $secret->save();
                $deviceMethod->secret()->associate($secret);
            }
            $device->pollingMethods()->save($deviceMethod);
        });

        $device->unsetRelation('pollingMethods');
        $setDeviceAvailability->execute($device);

        if ($request->wantsJson()) {
            $device->load('pollingMethods.secret');

            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'method' => $this->buildMethodData($device, $type),
            ]);
        }

        $toast->success($message);

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    private function reachabilityFailedResponse(
        Request $request,
        Device $device,
        PollingMethodType $type,
        ProbeResult $probeResult,
        ToastInterface $toast
    ): JsonResponse|RedirectResponse {
        $message = __('poller.reachability_failed', [
            'hostname' => $device->hostname,
            'method' => $type->label(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'unreachable',
                'message' => $message,
                'error_details' => $probeResult->errorMessage(),
            ], 422);
        }

        $toast->error($message);

        return redirect()->back();
    }
}
