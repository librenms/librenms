<?php

namespace App\Http\Requests;

use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretMode;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\PollingMethodRegistry;

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

        return is_string($type) ? PollingMethodType::tryFromKnown($type) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(PollingMethodRegistry $pollingMethods): array
    {
        $rules = [
            'method_type' => [Rule::requiredIf($this->isCreating()), Rule::enum(PollingMethodType::class)->except([PollingMethodType::Unknown])],
            'enabled' => ['boolean'],
            'affects_availability' => ['boolean'],
            'force_save' => ['boolean'],
            'settings' => ['array'],
        ];

        $type = $this->pollingType();
        if ($type === null) {
            return $rules;
        }

        $method = $pollingMethods->get($type);
        foreach ($method->definition()->rules() as $key => $rule) {
            $rules["settings.$key"] = $rule;
        }

        $secretTypes = $method->secretTypes();
        if (empty($secretTypes)) {
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

            $rules['secret_type'] = [Rule::requiredIf(count($secretTypes) > 1), 'nullable', Rule::in(array_column($secretTypes, 'value'))];
            foreach ($method->secretTypeFrom($this->input('secret_type'))?->definition()->rules() ?? [] as $key => $rule) {
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
        $type = $this->pollingType();

        if (! is_array($secretData) || ! $secretId || $type === null) {
            return;
        }

        $secretTypes = $this->container->make(PollingMethodRegistry::class)->get($type)->secretTypes();
        if (empty($secretTypes)) {
            return;
        }

        $source = Secret::resolveForTypes((int) $secretId, $secretTypes, $this->user());

        // an edited secret keeps its type, a new secret made from it has the same type unless another was chosen
        $secretType = SecretMode::tryFrom((string) $this->input('secret_mode')) === SecretMode::Edit
            ? $source->secret_type
            : (SecretType::tryFromKnown((string) $this->input('secret_type')) ?? $source->secret_type);

        $this->merge([
            'secret_type' => $secretType->value,
            'secret_data' => $source->secret_type === $secretType ? $secretType->definition()->unmask($secretData, $source->data) : $secretData,
        ]);
    }
}
