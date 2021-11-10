<?php

namespace App\Http\Controllers\Device;

use App\Actions\Device\SetDeviceAvailability;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\StorePollingMethodRequest;
use App\Http\Requests\UpdatePollingMethodRequest;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\SecretService;

class EditPollingController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SecretService $secretService,
    ) {}

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
            'configuredMethods' => $allMethods->filter(fn (array $m): bool => $m['configured'])->values(),
            'unconfiguredMethods' => $allMethods->filter(fn (array $m): bool => ! $m['configured'])->values(),
            'availableSecrets' => Secret::query()->orderBy('description')->get()->groupBy(
                fn (Secret $s): string => $s->secret_type->value
            ),
        ]);
    }

    private function buildMethodData(Device $device, PollingMethodType $type): array
    {
        $methodClass = $type->methodClass();
        $row = $device->pollingMethods->firstWhere('method_type', $type);
        $secret = $row?->secret;
        $canUnmaskSecrets = Gate::allows('unmask', Secret::class);
        $schema = $type->hasSecret() ? $type->secretClass()::getUiSchema() : [];
        $schemaFields = PollingMethodType::buildSchemaFields($schema);
        $settingsSchema = $methodClass::getSettingsSchema();
        $secretsForType = Secret::query()
            ->where('secret_type', $type->value)
            ->orderBy('description')
            ->get();
        $secretDescriptions = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string)$availableSecret->id => $availableSecret->description,
        ])->all();
        $secretFormDataById = $secretsForType->mapWithKeys(fn (Secret $availableSecret): array => [
            (string)$availableSecret->id => collect($schemaFields)->mapWithKeys(fn (array $field): array => [
                $field['key'] => $canUnmaskSecrets ? (string)data_get($availableSecret->data, $field['key'], '') : '',
            ])->all(),
        ])->all();

        return [
            'type' => $type->value,
            'label' => __('poller.methods.' . $type->value),
            'schema_fields' => $schemaFields,
            'schema_defaults' => collect($schema)->mapWithKeys(fn (array $field, string $key): array => [
                $key => $field['default'] ?? (isset($field['options']) ? array_key_first($field['options']) : ''),
            ])->all(),
            'settings_fields' => PollingMethodType::buildSchemaFields($settingsSchema, 'settingsData'),
            'settings' => $row?->settings ?? [],
            'affects_availability' => $row?->affects_availability ?? (bool)($methodClass::getDefaults()['affects_availability'] ?? false),
            'secret' => $secret,
            'secret_form_data' => collect($schema)->mapWithKeys(fn (array $field, string $key): array => [
                $key => $canUnmaskSecrets ? (string)data_get($secret?->data, $key, '') : '',
            ])->all(),
            'secret_descriptions' => $secretDescriptions,
            'secret_form_data_by_id' => $secretFormDataById,
            'usage_count' => $secret?->devices()->count() ?? 0,
            'configured' => $row !== null,
            'enabled' => $row?->enabled ?? false,
            'last_check_successful' => $row?->last_check_successful,
        ];
    }

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function store(StorePollingMethodRequest $request, Device $device, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validated();
        $type = $request->pollingType() ?? PollingMethodType::from($validated['method_type']);

        $secret = null;
        if ($type->hasSecret()) {
            $this->authorize('create', Secret::class);
            $secret = ($validated['credential_mode'] ?? 'existing') === 'existing'
                ? $this->resolveExistingSecret($validated['secret_id'] ?? null, $type)
                : $this->secretService->create(
                    $type,
                    $request->validatedSecretData(),
                    [
                        'description' => $validated['description'] ?: strtoupper($type->value) . ' ' . $request->user()?->user_id,
                        'default' => (bool) ($validated['default'] ?? false),
                    ]
                );
        }

        $methodClass = $type->methodClass();

        $row = new DevicePollingMethod([
            'device_id' => $device->device_id,
            'method_type' => $type,
            'enabled' => true,
            'affects_availability' => (bool)($methodClass::getDefaults()['affects_availability'] ?? false),
            'secret_id' => $secret?->id,
            'settings' => $this->buildSettings($methodClass, $request->validatedSettings()),
        ]);

        $device->pollingMethods()->save($row);

        $toast->success(__('poller.method_added'));

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * @throws ValidationException
     */
    private function resolveExistingSecret(?int $secretId, PollingMethodType $type): Secret
    {
        if (! $secretId) {
            throw ValidationException::withMessages([
                'secret_id' => __('poller.select_credential'),
            ]);
        }

        return $this->secretService->resolveExisting($secretId, $type);
    }

    // ---- Private helpers ----

    /**
     * @param  class-string  $methodClass
     */
    private function buildSettings(string $methodClass, array $validated): array
    {
        $schemaDefaults = collect($methodClass::getSettingsSchema())
            ->mapWithKeys(fn ($field, $key) => [
                $key => $field['default'] ?? (isset($field['options']) ? array_key_first($field['options']) : null),
            ])
            ->filter();

        return array_merge(
            $schemaDefaults->all(),
            collect($methodClass::getDefaults())->except('affects_availability')->all(),
            $validated
        );
    }

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function update(UpdatePollingMethodRequest $request, Device $device, string $methodType, ToastInterface $toast, SetDeviceAvailability $setDeviceAvailability): RedirectResponse
    {
        $this->authorize('update', $device);

        $type = PollingMethodType::tryFrom($methodType) ?? abort(404);
        $pollingMethod = $device->pollingMethods()->where('method_type', $type->value)->firstOrFail();
        $validated = $request->validated();

        if ($type->hasSecret() && array_key_exists('secret_id', $validated)) {
            $this->authorize('update', Secret::class);
            $pollingMethod->secret_id = $this->resolveExistingSecret((int)$validated['secret_id'], $type)->id;
        } elseif ($type->hasSecret() && $request->has('secret_data')) {
            $this->authorize('update', Secret::class);
            $mode = $validated['secret_update_mode'] ?? 'update';
            $pollingMethod->secret_id = $this->secretService->updateOrCreate(
                $pollingMethod,
                $type,
                $request->validatedSecretData(),
                $mode
            )->id;
        }

        $pollingMethod->setRelation('device', $device);

        $methodClass = $type->methodClass();

        $pollingMethod->enabled = (bool)($validated['enabled'] ?? true);
        $pollingMethod->affects_availability = (bool)($validated['affects_availability'] ?? false);
        $pollingMethod->settings = $this->mergeSettings($pollingMethod->settings ?? [], $validated['settings'] ?? [], $methodClass);

        $pollingMethod->save();

        $setDeviceAvailability->execute($device, false);
        $device->saveQuietly();

        $toast->success(__('poller.method_updated'));

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }

    /**
     * @param  class-string  $methodClass
     */
    private function mergeSettings(array $existing, array $validated, string $methodClass): array
    {
        $allowed = collect($methodClass::getSettingsSchema())->keys();

        return array_merge(
            $existing,
            collect($validated)->only($allowed)->all()
        );
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Device $device, string $methodType, ToastInterface $toast, SetDeviceAvailability $setDeviceAvailability): RedirectResponse
    {
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

        return redirect()->route('device.edit.polling', ['device' => $device, 'tab' => $type->value]);
    }
}
