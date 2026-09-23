<?php

namespace App\Http\Requests;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;

class UpdatePollingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation by replacing masked values with original database values.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->pollingType();
        /** @var \LibreNMS\Polling\Method\PollingMethodRegistry $registry */
        $registry = $this->container->make(\LibreNMS\Polling\Method\PollingMethodRegistry::class);
        $method = $type ? $registry->get($type) : null;
        if ($method?->hasSecret() && $this->has('secret_data')) {
            $device = $this->route('device');
            if ($device) {
                $secretId = $this->input('secret_id');
                $targetSecret = $secretId ? Secret::find($secretId) : null;
                $pollingMethod = $device->pollingMethods()->where('method_type', $type->value)->first();
                $oldData = $targetSecret ? $targetSecret->data : ($pollingMethod?->secret ? $pollingMethod->secret->data : []);

                $secretData = $this->input('secret_data');
                if (is_array($secretData)) {
                    foreach ($secretData as $key => $val) {
                        if ($val === '********') {
                            $secretData[$key] = data_get($oldData, $key, '');
                        }
                    }
                    $this->merge(['secret_data' => $secretData]);
                }
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(\LibreNMS\Polling\Method\PollingMethodRegistry $registry): array
    {
        $type = $this->pollingType();
        $method = $type ? $registry->get($type) : null;
        $isEditingSecret = $this->has('is_editing_secret') ? $this->boolean('is_editing_secret') : $this->has('secret_data');
        $secretUpdateMode = $this->input('secret_update_mode', 'update');

        $descriptionRules = ['nullable', 'string', 'max:255'];
        if ($isEditingSecret && $method?->hasSecret()) {
            $device = $this->route('device');
            /** @var Device|null $deviceModel */
            $deviceModel = $device instanceof Device ? $device : (is_numeric($device) ? DeviceCache::get($device) : null);
            if ($deviceModel && ! $deviceModel->exists) {
                $deviceModel = null;
            }
            $secretId = $this->input('secret_id');
            $targetSecretId = $secretId ? (int) $secretId : $deviceModel?->pollingMethods()->where('method_type', $type->value)->first()?->secret_id;
            $targetSecret = $targetSecretId ? Secret::find($targetSecretId) : null;
            $isShared = $targetSecret ? ($targetSecret->devices()->count() > 1) : false;

            if ($secretUpdateMode === 'create' && $isShared) {
                $descriptionRules = ['required', 'string', 'max:255', Rule::unique('secrets', 'description')];
            } elseif ($targetSecretId) {
                $descriptionRules = ['nullable', 'string', 'max:255', Rule::unique('secrets', 'description')->ignore($targetSecretId)];
            } else {
                $descriptionRules = ['nullable', 'string', 'max:255', Rule::unique('secrets', 'description')];
            }
        }

        $rules = [
            'enabled' => ['nullable', 'boolean'],
            'affects_availability' => ['nullable', 'boolean'],
            'secret_update_mode' => ['nullable', Rule::in(['update', 'create'])],
            'secret_id' => ['nullable', 'integer', 'exists:secrets,id'],
            'is_editing_secret' => ['nullable', 'boolean'],
            'description' => $descriptionRules,
            'force_save' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];

        if (! $method) {
            return $rules;
        }

        $rules = [
            ...$rules,
            ...collect($method->rules())
                ->mapWithKeys(fn (array|string $rule, string $key): array => ["settings.$key" => $rule])
                ->all(),
        ];

        $secretDefinition = SecretDefinition::for($method->secretType());
        if ($secretDefinition !== null && $this->has('secret_data')) {
            $rules = [
                ...$rules,
                ...collect($secretDefinition->rules())
                    ->mapWithKeys(fn (array|string $rule, string $key): array => ["secret_data.$key" => $rule])
                    ->all(),
            ];
        }

        return $rules;
    }

    public function pollingType(): ?PollingMethodType
    {
        $methodType = $this->route('methodType');

        return is_string($methodType) ? PollingMethodType::tryFrom($methodType) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedSettings(): array
    {
        return $this->validated('settings', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedSecretData(): array
    {
        return $this->validated('secret_data', []);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.unique' => __('The secret description has already been taken. Please choose a different description.'),
        ];
    }
}
