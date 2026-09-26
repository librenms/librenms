<?php

namespace App\Http\Controllers\Device;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;

class EditPollingController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PollingMethodRegistry $pollingMethods,
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $device->load('pollingMethods.secret');

        $allMethods = collect($this->pollingMethods->types())->map(
            fn (PollingMethodType $type): array => $this->buildMethodData($device, $type)
        );

        $configuredMethods = $allMethods->filter(fn (array $m): bool => $m['configured'])->values();
        $snmpConfigured = $configuredMethods->firstWhere('type', 'snmp');
        $defaultTab = ($snmpConfigured && ! empty($snmpConfigured['enabled'])) ? 'snmp' : $configuredMethods->first()['type'] ?? '';

        return view('device.edit.polling', [
            'device' => $device,
            'allMethods' => $allMethods,
            'configuredMethods' => $configuredMethods,
            'unconfiguredMethods' => $allMethods->filter(fn (array $m): bool => ! $m['configured'])->values(),
            'defaultTab' => $defaultTab,
            'availableSecrets' => Secret::query()
                ->when(auth()->user(), fn ($q, $user) => $q->hasAccess($user))
                ->orderBy('description')
                ->get()
                ->groupBy(fn (Secret $s): string => $s->secret_type->value),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMethodData(Device $device, PollingMethodType $type): array
    {
        $method = $this->pollingMethods->require($type);
        $definition = $this->pollingMethods->definition($type);
        /** @var DevicePollingMethod|null $row */
        $row = $device->pollingMethods->firstWhere('method_type', $type);
        $secret = $row?->secret;
        $canUnmaskSecrets = Gate::allows('unmask', Secret::class);
        $secretType = $method->secretType();
        $secretDef = SecretDefinition::for($secretType);
        $schema = $secretDef?->schema() ?? [];
        $schemaFields = $secretDef ? $secretDef->buildSchemaFields() : [];

        $secretsForType = $secretType ? Secret::query()
            ->when(auth()->user(), fn ($q, $user) => $q->hasAccess($user))
            ->where('secret_type', $secretType)
            ->withCount('devices')
            ->orderBy('description')
            ->get() : collect();

        $secretMeta = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string) $availableSecret->id => [
                'description' => $availableSecret->description,
                'usage_count' => $availableSecret->devices_count,
            ],
        ])->all();

        $schemaFieldKeys = array_column($schemaFields, 'key');
        $secretFormDataById = $secretsForType->mapWithKeys(
            fn (Secret $availableSecret): array => [
                (string) $availableSecret->id => (object) $this->extractFieldValues(
                    $this->unmaskSecretData($availableSecret, $canUnmaskSecrets),
                    $schemaFieldKeys
                ),
            ]
        )->all();

        return [
            'type' => $type->value,
            'label' => __('poller.methods.' . $type->value),
            'icon' => $definition->icon(),
            'schema_fields' => $schemaFields,
            'schema_defaults' => $secretDef?->schemaDefaults() ?? [],
            'settings_fields' => $definition->settingsFields($method->defaultConfig($device)),
            'settings' => [...array_fill_keys(array_keys($definition->fields()), ''), ...$row->settings ?? []],
            'affects_availability' => $row ? $row->affects_availability : $method->defaultConfig($device)->affectsAvailability,
            'secret' => $secret,
            'secret_form_data' => $secret
                ? $this->extractFieldValues($this->unmaskSecretData($secret, $canUnmaskSecrets), array_keys($schema))
                : null,
            'secret_meta' => $secretMeta,
            'secret_form_data_by_id' => $secretFormDataById,
            'usage_count' => $secret?->devices()->count() ?? 0,
            'configured' => $row !== null,
            'enabled' => $row ? $row->enabled : true,
            'last_check_successful' => $row?->last_check_successful,
        ];
    }

    /**
     * Returns a secret's decrypted data, or an empty array if it can't/shouldn't be unmasked.
     *
     * @return array<string, mixed>
     */
    private function unmaskSecretData(?Secret $secret, bool $canUnmask): array
    {
        if (! $secret || ! $canUnmask) {
            return [];
        }

        return $secret->data ?? [];
    }

    /**
     * Pulls the given keys out of a data array as strings, for populating a form.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     * @return array<string, string>
     */
    private function extractFieldValues(array $data, array $keys): array
    {
        return collect($keys)->mapWithKeys(fn (string $key): array => [
            $key => (string) data_get($data, $key, ''),
        ])->all();
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
            'affects_availability' => $this->pollingMethods->require($type)->defaultConfig($device)->affectsAvailability,
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

        $toast->success(__('poller.method_removed'));

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_removed'),
            ]);
        }

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
        $method = $this->pollingMethods->require($type);

        if ($request->has('enabled')) {
            $deviceMethod->enabled = $request->boolean('enabled');
        }
        if ($request->has('affects_availability')) {
            $deviceMethod->affects_availability = $request->boolean('affects_availability');
        }
        if ($request->has('settings')) {
            $deviceMethod->settings = $this->pollingMethods->definition($type)->filterOverrides($request->validated('settings', []));
        }

        $secret = $this->resolveSecret($request, $method);
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

        $toast->success($message);

        if ($request->wantsJson()) {
            $device->load('pollingMethods.secret');

            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'method' => $this->buildMethodData($device, $type),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * The secret selected by the request, unsaved when new or edited. Null keeps the current secret.
     *
     * @throws AuthorizationException
     */
    private function resolveSecret(SavePollingMethodRequest $request, PollingMethod $method): ?Secret
    {
        $secretType = $method->secretType();
        $mode = $secretType ? $request->secretMode() : null;

        if ($mode === 'new') {
            $this->authorize('create', Secret::class);

            return new Secret([
                'secret_type' => $secretType,
                'description' => $request->validated('description'),
                'data' => $request->validated('secret_data', []),
            ]);
        }

        if ($mode === 'existing' || $mode === 'edit') {
            $secret = Secret::resolveForType((int) $request->validated('secret_id'), $secretType);

            if ($mode === 'edit') {
                $this->authorize('update', $secret);
                $secret->data = $request->validated('secret_data', []);
                $secret->description = $request->validated('description') ?: $secret->description;
            }

            return $secret;
        }

        return null;
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
            'method' => __('poller.methods.' . $type->value),
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
