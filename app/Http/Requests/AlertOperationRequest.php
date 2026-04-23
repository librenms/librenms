<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlertOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $segments = $this->input('segments');
        if (! is_array($segments)) {
            return;
        }
        foreach ($segments as $i => $seg) {
            if (! is_array($seg)) {
                continue;
            }
            if (($seg['escalation_step_to'] ?? null) === '') {
                $segments[$i]['escalation_step_to'] = null;
            }
        }
        $this->merge(['segments' => $segments]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'default_operation_step_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'segments' => ['required', 'array', 'min:1'],
            'segments.*.position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'segments.*.escalation_step_from' => ['required', 'integer', 'min:1'],
            'segments.*.escalation_step_to' => ['nullable', 'integer', 'min:1'],
            'segments.*.start_in_seconds' => ['required', 'integer', 'min:0'],
            'segments.*.step_duration_seconds' => ['required', 'integer', 'min:0'],
            'segments.*.transports' => ['required', 'array', 'min:1'],
            'segments.*.transports.*' => ['nullable'],
        ];
    }
}
