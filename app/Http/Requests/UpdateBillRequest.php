<?php

namespace App\Http\Requests;

use App\Facades\LibrenmsConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillRequest extends FormRequest
{
    /** @var array<string, int> unit => exponent of the billing base */
    public const QUOTA_UNITS = ['MB' => 2, 'GB' => 3, 'TB' => 4];
    /** @var array<string, int> unit => exponent of the billing base */
    public const CDR_UNITS = ['Kbps' => 1, 'Mbps' => 2, 'Gbps' => 3];

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
            'bill_quota' => ['nullable', 'numeric', 'min:0'],
            'bill_quota_type' => ['nullable', Rule::in(array_keys(self::QUOTA_UNITS))],
            'bill_cdr' => ['nullable', 'numeric', 'min:0'],
            'bill_cdr_type' => ['nullable', Rule::in(array_keys(self::CDR_UNITS))],
            'dir_95th' => ['nullable', Rule::in(['in', 'out', 'agg'])],
            'bill_custid' => ['nullable', 'string', 'max:64'],
            'bill_ref' => ['nullable', 'string', 'max:64'],
            'bill_notes' => ['nullable', 'string', 'max:256'],
        ];
    }

    /**
     * Validated bill attributes with the quota/cdr converted to bytes/bits.
     *
     * @return array{bill_name: string, bill_day: int, bill_type: string, bill_quota: int, bill_cdr: int, dir_95th: string|null, bill_custid: string, bill_ref: string, bill_notes: string}
     */
    public function billAttributes(): array
    {
        $validated = $this->validated();
        $isQuota = $validated['bill_type'] === 'quota';

        return [
            'bill_name' => $validated['bill_name'],
            'bill_day' => (int) $validated['bill_day'],
            'bill_type' => $validated['bill_type'],
            'bill_quota' => $isQuota ? $this->toBaseUnits($validated['bill_quota'] ?? 0, self::QUOTA_UNITS[$validated['bill_quota_type'] ?? 'MB']) : 0,
            'bill_cdr' => $isQuota ? 0 : $this->toBaseUnits($validated['bill_cdr'] ?? 0, self::CDR_UNITS[$validated['bill_cdr_type'] ?? 'Mbps']),
            'dir_95th' => $validated['dir_95th'] ?? null,
            'bill_custid' => (string) ($validated['bill_custid'] ?? ''),
            'bill_ref' => (string) ($validated['bill_ref'] ?? ''),
            'bill_notes' => (string) ($validated['bill_notes'] ?? ''),
        ];
    }

    /**
     * Convert a value in the given unit (exponent of the billing base) to bits or bytes
     */
    private function toBaseUnits(int|float|string $value, int $exponent): int
    {
        return (int) round((float) $value * (int) LibrenmsConfig::get('billing.base', 1000) ** $exponent);
    }
}
