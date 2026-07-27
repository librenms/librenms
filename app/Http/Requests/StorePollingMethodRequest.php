<?php

namespace App\Http\Requests;

use App\Models\Device;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\PollingMethodDefinition;

class StorePollingMethodRequest extends FormRequest
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
            'method_type' => ['required', Rule::enum(PollingMethodType::class)],
            'credential_mode' => ['nullable', Rule::in(['existing', 'new'])],
            'secret_id' => ['nullable', 'integer', 'exists:secrets,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'default' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];

        $type = $this->pollingType();

        if ($type) {
            $definition = PollingMethodDefinition::for($type);
            $rules = [
                ...$rules,
                ...collect($definition->rules())
                    ->mapWithKeys(fn (array|string $rule, string $key): array => ["settings.$key" => $rule])
                    ->all(),
            ];

            $secretDefinition = $definition->secretDefinition();
            if ($secretDefinition !== null && $this->input('credential_mode', 'existing') === 'new') {
                $rules = [
                    ...$rules,
                    ...collect($secretDefinition->rules())
                        ->mapWithKeys(fn (array|string $rule, string $key): array => ["secret_data.$key" => $rule])
                        ->all(),
                ];
            }
        }

        return [
            ...$rules,
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->pollingType();

            if (! $type) {
                return;
            }

            /** @var Device|null $device */
            $device = $this->route('device');

            if ($device?->pollingMethods()->where('method_type', $type->value)->exists()) {
                $validator->errors()->add('method_type', __('poller.method_exists'));
            }

            if (PollingMethodDefinition::hasSecret($type) && $this->input('credential_mode', 'existing') === 'existing' && ! $this->input('secret_id')) {
                $validator->errors()->add('secret_id', __('poller.select_credential'));
            }
        });
    }

    public function pollingType(): ?PollingMethodType
    {
        $methodType = $this->input('method_type');

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
}
