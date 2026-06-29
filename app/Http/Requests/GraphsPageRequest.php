<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GraphsPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'regex:/^[a-zA-Z0-9]+_[a-zA-Z0-9_.-]+$/'],
            'from' => ['nullable', 'string', 'regex:/^[-a-zA-Z0-9_ :]+$/'],
            'to' => ['nullable', 'string', 'regex:/^[-a-zA-Z0-9_ :]+$/'],
            'widescreen' => ['nullable', 'string', 'in:yes,no'],
            'legend' => ['nullable', 'string', 'in:yes,no'],
            'previous' => ['nullable', 'string', 'in:yes,no'],
            'showcommand' => ['nullable', 'string', 'in:yes,no'],
            'port_speed_zoom' => ['nullable', 'in:0,1'],
            'device' => ['nullable', 'integer'],
            'id' => ['nullable', 'integer'],
            'width' => ['nullable', 'integer', 'min:10'],
            'height' => ['nullable', 'integer', 'min:10'],

            // Collectd parameters
            'c_plugin' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'c_plugin_instance' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'c_type' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'c_type_instance' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],

            // Sensor parameters
            'sensor' => ['nullable', 'integer'],

            // Generic parameters commonly used by legacy graph scripts
            'in' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'out' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'inverse' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
            'float_precision' => ['nullable', 'integer'],
            'total' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
            'details' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
            'aggregate' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $validateItem = function (string $key, mixed $value) use ($validator, &$validateItem): void {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $validateItem($key, $k);
                        $validateItem($key, $v);
                    }
                } elseif (is_string($value)) {
                    if (! preg_match('/^[-a-zA-Z0-9_.: \/+]*$/', $value)) {
                        $validator->errors()->add($key, 'The parameter value contains invalid characters.');
                    }
                }
            };

            foreach ($this->all() as $key => $value) {
                // Validate key
                if (! preg_match('/^[a-zA-Z0-9_.-]+$/', $key)) {
                    $validator->errors()->add($key, 'The parameter key contains invalid characters.');
                }
                $validateItem($key, $value);
            }

            // Check if graph type auth file exists
            $typeInput = $this->input('type');
            if (is_string($typeInput) && str_contains($typeInput, '_')) {
                [$typePart] = explode('_', $typeInput, 2);
                $authPath = base_path("includes/html/graphs/{$typePart}/auth.inc.php");
                if (! is_file($authPath)) {
                    $validator->errors()->add('type', 'The specified graph type is invalid.');
                }
            }
        });
    }
}
