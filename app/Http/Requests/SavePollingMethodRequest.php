<?php

namespace App\Http\Requests;

use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretMode;

/**
 * Add (POST, method_type input) or update (PUT, methodType route) a device's polling method.
 *
 * secret_mode (see SecretMode), for methods with a secret: existing, new or edit.
 * Omitted keeps the current secret (update only). A new secret copies masked values from secret_id.
 */
class SavePollingMethodRequest extends FormRequest
{
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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'method_type' => [Rule::requiredIf($this->isCreating()), Rule::enum(PollingMethodType::class)],
            'enabled' => ['boolean'],
            'affects_availability' => ['boolean'],
            'force_save' => ['boolean'],
            'settings' => ['array'],
        ];

        $type = $this->pollingType();
        if ($type === null) {
            return $rules;
        }

        foreach ($type->definition()->rules() as $key => $rule) {
            $rules["settings.$key"] = $rule;
        }

        $secretDefinition = $type->method()->secretType()?->definition();
        if ($secretDefinition === null) {
            return $rules;
        }

        $mode = SecretMode::tryFrom((string) $this->input('secret_mode'));
        $rules['secret_mode'] = [Rule::requiredIf($this->isCreating()), 'nullable', Rule::enum(SecretMode::class)->only([SecretMode::Existing, SecretMode::New, SecretMode::Edit])];
        $rules['secret_id'] = ['required_if:secret_mode,existing,edit', 'nullable', 'integer'];

        if ($mode === SecretMode::New || $mode === SecretMode::Edit) {
            $unique = Rule::unique('secrets', 'description');
            $rules['description'] = $mode === SecretMode::New
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
        $secretType = $this->pollingType()?->method()->secretType();

        if (! is_array($secretData) || ! $secretId || $secretType === null) {
            return;
        }

        $source = Secret::resolveForType((int) $secretId, $secretType, $this->user());

        $this->merge(['secret_data' => $secretType->definition()->unmask($secretData, $source->data)]);
    }
}
