<?php

namespace App\Http\Requests;

use App\Models\Bill;
use Illuminate\Validation\Rule;

class StoreBillRequest extends UpdateBillRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Bill::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'source_type' => ['required_with:source_id', Rule::in(array_keys(Bill::sourceTypes()))],
            'source_id' => ['nullable', 'integer'],
        ]);
    }
}
