<?php

namespace App\Http\Requests;

use App\Models\Device;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LibreNMS\Enum\PollingMethodType;

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
    public function rules(\LibreNMS\Polling\Method\PollingMethodRegistry $registry): array
    {
        $rules = [
            'method_type' => ['required', Rule::enum(PollingMethodType::class)],
            'credential_mode' => ['nullable', Rule::in(['existing', 'new'])],
            'secret_id' => ['nullable', 'integer', 'exists:secrets,id'],
            'description' => [
                Rule::excludeIf($this->input('credential_mode', 'existing') !== 'new'),
                'required',
                'string',
                'max:255',
                'unique:secrets,description',
            ],
            'default' => [
                Rule::excludeIf($this->input('credential_mode', 'existing') !== 'new'),
                'nullable',
                'boolean',
            ],
            'force_save' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];

        $type = $this->pollingType();

        if ($type) {
            $definition = $registry->definition($type);
            if ($definition) {
                $rules = [
                    ...$rules,
                    ...collect($definition->rules())
                        ->mapWithKeys(fn (array|string $rule, string $key): array => ["settings.$key" => $rule])
                        ->all(),
                ];

                $secretDefinition = $registry->secretDefinition($type);
                if ($secretDefinition !== null && $this->input('credential_mode', 'existing') === 'new') {
                    $rules = [
                        ...$rules,
                        ...collect($secretDefinition->rules())
                            ->mapWithKeys(fn (array|string $rule, string $key): array => ["secret_data.$key" => $rule])
                            ->all(),
                    ];
                }
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

            /** @var \LibreNMS\Polling\Method\PollingMethodRegistry $registry */
            $registry = $this->container->make(\LibreNMS\Polling\Method\PollingMethodRegistry::class);
            if ($registry->hasSecret($type) && $this->input('credential_mode', 'existing') === 'existing' && ! $this->input('secret_id')) {
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
