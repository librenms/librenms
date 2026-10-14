<?php

namespace App\Http\Requests;

use App\Models\AlertSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\MaintenanceBehavior;

class AlertScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $schedule = $this->route('alert_schedule');

        return $schedule instanceof AlertSchedule
            ? $this->user()->can('update', $schedule)
            : $this->user()->can('create', AlertSchedule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'recurring' => 'boolean',
            'behavior' => ['required', Rule::enum(MaintenanceBehavior::class)],
            'maps' => 'required|array|min:1',
            'maps.*' => 'regex:/^[gl]?\d+$/',
            // one-time schedule
            'start' => 'exclude_if:recurring,true|required|date',
            'duration' => 'exclude_if:recurring,true|nullable|regex:/^\d+:\d{1,2}$/',
            'end' => 'exclude_if:recurring,true|required_without:duration|nullable|date|after:start',
            // recurring schedule
            'start_recurring_dt' => 'exclude_unless:recurring,true|required|date_format:Y-m-d',
            'end_recurring_dt' => 'exclude_unless:recurring,true|nullable|date_format:Y-m-d|after_or_equal:start_recurring_dt',
            'start_recurring_hr' => 'exclude_unless:recurring,true|required|date_format:H:i',
            'end_recurring_hr' => 'exclude_unless:recurring,true|required|date_format:H:i',
            'recurring_day' => 'exclude_unless:recurring,true|nullable|array',
            'recurring_day.*' => 'integer|between:1,7',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'recurring' => $this->boolean('recurring'),
        ]);
    }
}
