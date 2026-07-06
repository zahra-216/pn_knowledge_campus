<?php

namespace App\Http\Requests\OfficeHours;

use App\Models\OfficeHour;
use Illuminate\Foundation\Http\FormRequest;

/**
 * PUT /api/v1/admin/office-hours — bulk update by day. Body shape:
 * { "hours": { "monday": { "is_open": true, "opens_at": "08:30", "closes_at": "17:00" }, ... } }
 * Mirrors UpdateSettingsRequest's bulk-by-key shape and the same
 * "only known keys, whole thing validated together" approach.
 */
class UpdateOfficeHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by settings.edit in the controller
    }

    public function rules(): array
    {
        return [
            'hours' => ['required', 'array', 'min:1'],
            'hours.*' => ['array'],
            'hours.*.is_open' => ['sometimes', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i,H:i:s'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i,H:i:s'],
            'hours.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('hours', []) as $day => $entry) {
                if (! in_array($day, OfficeHour::DAYS, true)) {
                    $validator->errors()->add("hours.{$day}", "\"{$day}\" is not a recognized day of the week.");

                    continue;
                }

                if (! empty($entry['opens_at']) && ! empty($entry['closes_at']) && $entry['closes_at'] <= $entry['opens_at']) {
                    $validator->errors()->add("hours.{$day}.closes_at", 'Closing time must be after opening time.');
                }
            }
        });
    }
}
