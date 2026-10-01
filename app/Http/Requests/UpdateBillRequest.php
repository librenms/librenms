<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('bill')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bill_name' => ['required', 'string', 'max:255'],
            'bill_type' => ['required', Rule::in(['quota', 'cdr'])],
            'bill_day' => ['required', 'integer', 'between:1,31'],
            'bill_quota' => ['nullable', 'numeric'],
            'bill_quota_type' => ['nullable', Rule::in(['MB', 'GB', 'TB'])],
            'bill_cdr' => ['nullable', 'numeric'],
            'bill_cdr_type' => ['nullable', Rule::in(['Kbps', 'Mbps', 'Gbps'])],
            'dir_95th' => ['nullable', Rule::in(['in', 'out', 'agg'])],
            'bill_custid' => ['nullable', 'string', 'max:64'],
            'bill_ref' => ['nullable', 'string', 'max:64'],
            'bill_notes' => ['nullable', 'string', 'max:256'],
        ];
    }
}
