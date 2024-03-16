<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class CustomMapSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();  // TODO permissions
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'node_align' => 'integer',
            'reverse_arrows' => 'boolean',
            'edge_separation' => 'integer',
            'width_type' => 'in:px,%',
            'width' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! preg_match('/^(\d+)(px|%)$/', $value, $matches)) {
                        $fail(__('map.custom.edit.validate.width_format'));
                    } elseif ($matches[2] == 'px' && $matches[1] < 200) {
                        $fail(__('map.custom.edit.validate.width_pixels'));
                    } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
                        $fail(__('map.custom.edit.validate.width_percent'));
                    }
                },
            ],
            'height_type' => 'in:px,%',
            'height' => [
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! preg_match('/^(\d+)(px|%)$/', $value, $matches)) {
                        $fail(__('map.custom.edit.validate.height_format'));
                    } elseif ($matches[2] == 'px' && $matches[1] < 200) {
                        $fail(__('map.custom.edit.validate.height_pixels'));
                    } elseif ($matches[2] == '%' && ($matches[1] < 10 || $matches[1] > 100)) {
                        $fail(__('map.custom.edit.validate.height_percent'));
                    }
                },
            ],
            'legend_x' => 'integer',
            'legend_y' => 'integer',
            'legend_steps' => 'integer',
            'legend_font_size' => 'integer',
            'legend_hide_invalid' => 'boolean',
            'legend_hide_overspeed' => 'boolean',
        ];
    }
}
