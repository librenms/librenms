<?php

namespace App\Http\Requests;

use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;

/**
 * Add (POST, method_type input) or update (PUT, methodType route) a device's polling method.
 *
 * secret_mode, for methods with a secret:
 *   existing: use secret_id
 *   new:      create a secret from description and secret_data (masked values are copied from secret_id)
 *   edit:     update secret_id with secret_data and description
 *   omitted:  keep the current secret (update only)
 */
class SavePollingMethodRequest extends FormRequest
{
    public const SECRET_MODES = ['existing', 'new', 'edit'];

    public function authorize(): bool
    {
        return true; // authorized by the controller
    }

    public function isCreating(): bool
    {
        return $this->route('methodType') === null;
    }

    public function pollingType(): ?PollingMethodType
    {
        $type = $this->route('methodType') ?? $this->input('method_type');

        return is_string($type) ? PollingMethodType::tryFrom($type) : null;
    }

    public function secretMode(): ?string
    {
        return $this->validated('secret_mode');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(PollingMethodRegistry $registry): array
    {
        $rules = [
            'method_type' => [Rule::requiredIf($this->isCreating()), Rule::enum(PollingMethodType::class)],
            'enabled' => ['boolean'],
            'affects_availability' => ['boolean'],
            'force_save' => ['boolean'],
            'settings' => ['array'],
        ];

        $type = $this->pollingType();
        if ($type === null || ! $registry->has($type)) {
            return $rules;
        }

        foreach ($registry->definition($type)->rules() as $key => $rule) {
            $rules["settings.$key"] = $rule;
        }

        $secretDefinition = SecretDefinition::for($registry->require($type)->secretType());
        if ($secretDefinition === null) {
            return $rules;
        }

        $mode = $this->input('secret_mode');
        $rules['secret_mode'] = [Rule::requiredIf($this->isCreating()), 'nullable', Rule::in(self::SECRET_MODES)];
        $rules['secret_id'] = ['required_if:secret_mode,existing,edit', 'nullable', 'integer'];

        if ($mode === 'new' || $mode === 'edit') {
            $unique = Rule::unique('secrets', 'description');
            $rules['description'] = $mode === 'new'
                ? ['required', 'string', 'max:255', $unique]
                : ['nullable', 'string', 'max:255', $unique->ignore($this->input('secret_id'))];

            foreach ($secretDefinition->rules() as $key => $rule) {
                $rules["secret_data.$key"] = $rule;
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->pollingType();
            $device = $this->route('device');

            if ($type && $this->isCreating() && $device instanceof Device && $device->pollingMethod($type)) {
                $validator->errors()->add('method_type', __('poller.method_exists'));
            }
        });
    }

    /**
     * Masked secret values mean "unchanged": restore them from the source secret before validation.
     * Only a secret this user can access is used.
     */
    protected function prepareForValidation(): void
    {
        $secretData = $this->input('secret_data');
        $secretId = $this->input('secret_id');
        $method = $this->pollingMethod();

        if (! is_array($secretData) || ! $secretId || ! $method?->hasSecret()) {
            return;
        }

        $source = Secret::resolveForType((int) $secretId, $method->secretType(), $this->user());

        foreach ($secretData as $key => $value) {
            if ($value === Secret::MASK) {
                $secretData[$key] = data_get($source->data, $key, '');
            }
        }

        $this->merge(['secret_data' => $secretData]);
    }

    private function pollingMethod(): ?PollingMethod
    {
        $type = $this->pollingType();

        return $type ? $this->container->make(PollingMethodRegistry::class)->get($type) : null;
    }
}
