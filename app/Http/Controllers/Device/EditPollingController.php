<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\SetDeviceAvailability;
use App\Facades\LibrenmsConfig;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StorePollingMethodRequest;
use App\Http\Requests\UpdatePollingMethodRequest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;

class EditPollingController
{
    use AuthorizesRequests;

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
        $definition = $type->definition();
        /** @var DevicePollingMethod|null $row */
        $row = $device->pollingMethods->firstWhere('method_type', $type);
        $secret = $row?->secret;
        $canUnmaskSecrets = Gate::allows('unmask', Secret::class);
        $secretDef = $definition->secretDefinition();
        $schema = $secretDef?->schema() ?? [];
        $schemaFields = $secretDef ? $secretDef->buildSchemaFields() : [];
        $settingsFields = $definition->buildSchemaFields(dataVar: 'settingsData');
        $secretsForType = Secret::query()
            ->when(auth()->user(), fn ($q, $user) => $q->hasAccess($user))
            ->where('secret_type', $type->value)
            ->withCount('devices')
            ->orderBy('description')
            ->get();
        $secretMeta = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string) $availableSecret->id => [
                'description' => $availableSecret->description,
                'usage_count' => $availableSecret->devices_count,
            ],
        ])->all();
        $secretFormDataById = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string) $availableSecret->id => (object) collect($schemaFields)->mapWithKeys(fn (array $field): array => [
                $field['key'] => $canUnmaskSecrets ? (string) data_get($availableSecret->data, $field['key'], '') : '',
            ])->all(),
        ])->all();

        $settingsDefaults = $definition->schemaDefaults();

        return [
            'type' => $type->value,
            'label' => __('poller.methods.' . $type->value),
            'icon' => $definition->icon(),
            'schema_fields' => $schemaFields,
            'schema_defaults' => $secretDef?->schemaDefaults() ?? [],
            'settings_fields' => $settingsFields,
            'settings_defaults' => $settingsDefaults,
            'settings' => array_merge(
                $settingsDefaults,
                $row->settings ?? [],
                $type === PollingMethodType::Snmp ? ['port_association_mode' => PortAssociationMode::getName($device->port_association_mode) ?? LibrenmsConfig::get('default_port_association_mode', 'ifIndex')] : []
            ),
            'affects_availability' => $row ? $row->affects_availability : $definition->defaultAffectsAvailability(),
            'secret' => $secret,
            'secret_form_data' => collect($schema)->mapWithKeys(fn (array $field, string $key): array => [
                $key => $canUnmaskSecrets ? (string) data_get($secret?->data, $key, '') : '',
            ])->all(),
            'secret_meta' => $secretMeta,
            'secret_form_data_by_id' => $secretFormDataById,
            'usage_count' => $secret?->devices()->count() ?? 0,
            'configured' => $row !== null,
            'enabled' => $row ? $row->enabled : true,
            'last_check_successful' => $row?->last_check_successful,
        ];
    }

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function store(StorePollingMethodRequest $request, Device $device, ToastInterface $toast): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validated();
        $type = $request->pollingType() ?? PollingMethodType::from($validated['method_type']);
        $definition = $type->definition();

        if ($definition->secretDefinition() !== null) {
            $this->authorize('create', Secret::class);
        }

        $credentialMode = $validated['credential_mode'] ?? 'existing';
        $secretId = isset($validated['secret_id']) ? (int) $validated['secret_id'] : null;
        if ($definition->secretDefinition() !== null && $credentialMode === 'existing' && ! $secretId) {
            throw ValidationException::withMessages([
                'secret_id' => __('poller.select_credential'),
            ]);
        }

        $row = DevicePollingMethod::saveForDevice(
            device: $device,
            type: $type,
            settings: $request->validatedSettings(),
            enabled: true,
            affectsAvailability: $definition->defaultAffectsAvailability(),
        );

        if ($definition->secretDefinition() !== null) {
            if ($credentialMode === 'existing' && $secretId !== null) {
                $secret = Secret::resolveForType($secretId, $type);
                $row->secret()->associate($secret)->save();
            } else {
                $description = $validated['description'];
                $secret = Secret::create([
                    'secret_type' => $type->value,
                    'description' => $description,
                    'default' => (bool) ($validated['default'] ?? false),
                    'data' => $request->validatedSecretData(),
                ]);
                $row->secret()->associate($secret)->save();
            }
        }

        if ($type === PollingMethodType::Snmp && isset($row->settings['port_association_mode'])) {
            $device->port_association_mode = PortAssociationMode::getId($row->settings['port_association_mode']) ?? 1;
            $device->saveQuietly();
        }

        $toast->success(__('poller.method_added'));

        if ($request->wantsJson()) {
            $device->load('pollingMethods.secret');

            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_added'),
                'method' => $this->buildMethodData($device, $type),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function update(
        UpdatePollingMethodRequest $request,
        Device $device,
        string $methodType,
        ToastInterface $toast,
        SetDeviceAvailability $setDeviceAvailability
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $device);

        $type = PollingMethodType::tryFrom($methodType) ?? abort(404);
        /** @var DevicePollingMethod $pollingMethod */
        $pollingMethod = $device->pollingMethods()->where('method_type', $type->value)->firstOrFail();
        $validated = $request->validated();

        $secretId = null;
        if ($type->hasSecret()) {
            if (array_key_exists('secret_id', $validated)) {
                $this->authorize('update', Secret::class);
                $secretId = (int) $validated['secret_id'];
                if (! $secretId) {
                    throw ValidationException::withMessages([
                        'secret_id' => __('poller.select_credential'),
                    ]);
                }
            } elseif ($request->has('secret_data')) {
                $this->authorize('update', Secret::class);
            }
        }

        $pollingMethod->setRelation('device', $device);

        $pollingMethod = DevicePollingMethod::saveForDevice(
            device: $device,
            type: $type,
            settings: $validated['settings'] ?? [],
            enabled: (bool) ($validated['enabled'] ?? true),
            affectsAvailability: (bool) ($validated['affects_availability'] ?? false),
        );

        if ($type->hasSecret()) {
            $isEditingSecret = (bool) $request->input('is_editing_secret', $request->has('secret_data'));
            $secretData = $request->has('secret_data') ? $request->validatedSecretData() : null;
            $mode = $validated['secret_update_mode'] ?? 'update';
            $description = $validated['description'] ?? null;

            if ($secretId !== null && ! $isEditingSecret) {
                $secret = Secret::resolveForType($secretId, $type);
                $pollingMethod->secret()->associate($secret)->save();
            } elseif ($secretData !== null) {
                $targetSecret = $secretId !== null ? Secret::resolveForType($secretId, $type) : $pollingMethod->secret;

                if ($mode === 'create' || ! $targetSecret) {
                    $secret = Secret::create([
                        'secret_type' => $type->value,
                        'description' => $description ?: ('Custom ' . strtoupper($type->value) . ' (' . $device->hostname . ')'),
                        'default' => false,
                        'data' => $secretData,
                    ]);
                    $pollingMethod->secret()->associate($secret)->save();
                } else {
                    $updateAttributes = ['data' => $secretData];
                    if ($description !== null && $description !== '') {
                        $updateAttributes['description'] = $description;
                    }
                    $targetSecret->update($updateAttributes);
                    $pollingMethod->secret()->associate($targetSecret)->save();
                }
            }

            $pollingMethod->invalidateConfigCache();
        }

        if ($type === PollingMethodType::Snmp && isset($pollingMethod->settings['port_association_mode'])) {
            $device->port_association_mode = PortAssociationMode::getId($pollingMethod->settings['port_association_mode']) ?? 1;
        }

        $setDeviceAvailability->execute($device, false);
        $device->saveQuietly();

        $toast->success(__('poller.method_updated'));

        if ($request->wantsJson()) {
            $device->load('pollingMethods.secret');

            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_updated'),
                'method' => $this->buildMethodData($device, $type),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(
        Device $device,
        string $methodType,
        ToastInterface $toast,
        SetDeviceAvailability $setDeviceAvailability
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', $device);

        $type = PollingMethodType::tryFrom($methodType) ?? abort(404);
        $pollingMethod = $device->pollingMethods()->where('method_type', $type->value)->firstOrFail();

        if ($type->hasSecret()) {
            $this->authorize('delete', Secret::class);
        }

        $pollingMethod->delete();

        $setDeviceAvailability->execute($device, false);
        $device->saveQuietly();

        $toast->success(__('poller.method_removed'));

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('poller.method_removed'),
            ]);
        }

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }
}
