<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;

class UpdatePollingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            /** @var class-string<\App\LibreNMS\Polling\Secrets\SecretData> $secretClass */
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
