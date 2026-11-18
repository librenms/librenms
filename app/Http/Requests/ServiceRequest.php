<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Services;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service
            ? $this->user()->can('update', $service)
            : $this->user()->can('create', Service::class);
    }

    public function rules(): array
    {
        $rules = [
            'service_name' => 'nullable|string|max:255',
            'service_desc' => 'nullable|string',
            'service_ip' => 'nullable|string|max:255',
            'service_param' => 'nullable|string',
            'service_ignore' => 'sometimes|boolean',
            'service_disabled' => 'sometimes|boolean',
        ];

        if ($this->isMethod('post')) {
            $rules['device_id'] = 'required|integer|exists:devices,device_id';
            $rules['service_type'] = ['required', 'string', Rule::in(Services::list())];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributesForService(): array
    {
        $attributes = $this->safe()->except('device_id');

        // Older consumers expect plain-text names and descriptions.
        foreach (['service_name', 'service_desc'] as $field) {
            if (array_key_exists($field, $attributes)) {
                $attributes[$field] = strip_tags((string) $attributes[$field]);
            }
        }

        return $attributes;
    }
}
