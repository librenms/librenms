<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\SecretData;

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
        if ($type && $type->hasSecret() && $this->has('secret_data')) {
            $device = $this->route('device');
            if ($device) {
                $pollingMethod = $device->pollingMethods()->where('method_type', $type->value)->first();
                $oldData = $pollingMethod?->secret?->data ?? [];

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
    public function rules(): array
    {
        $rules = [
            'enabled' => ['nullable', 'boolean'],
            'affects_availability' => ['nullable', 'boolean'],
            'secret_update_mode' => ['nullable', Rule::in(['update', 'create'])],
            'secret_id' => ['nullable', 'integer', 'exists:secrets,id'],
            'force_save' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];

        $type = $this->pollingType();

        if (! $type) {
            return $rules;
        }

        $methodClass = $type->methodClass();
        $rules = [
            ...$rules,
            ...collect($methodClass::getRules())
                ->mapWithKeys(fn (array|string $rule, string $key): array => ["settings.$key" => $rule])
                ->all(),
        ];

        if ($type->hasSecret() && $this->has('secret_data')) {
            /** @var class-string<SecretData> $secretClass */
            $secretClass = $type->secretClass();
            $rules = [
                ...$rules,
                ...collect($secretClass::rules())
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

    public function validatedSettings(): array
    {
        return $this->validated('settings', []);
    }

    public function validatedSecretData(): array
    {
        return $this->validated('secret_data', []);
    }
}
