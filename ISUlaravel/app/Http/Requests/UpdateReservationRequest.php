<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'description' => 'nullable|string',
            'venue_id' => 'required|exists:venues,id',
            'area_id' => 'nullable|integer|exists:areas,id',
            'area_name' => 'nullable|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'capacity' => 'required|integer|min:1',
            'end_date' => 'nullable|date|after_or_equal:date',
            'event_approval_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'existing_event_approval_file' => 'nullable|string',
            'force' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'The end date must be after the start date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for ONLY nullable fields
        // Don't convert required fields (title, venue_id, date, start_time, end_time, capacity)
        if ($this->has('description') && $this->input('description') === '') {
            $this->merge(['description' => null]);
        }
        if ($this->has('end_date') && $this->input('end_date') === '') {
            $this->merge(['end_date' => null]);
        }
        if ($this->has('area_name') && $this->input('area_name') === '') {
            $this->merge(['area_name' => null]);
        }
        if ($this->has('area_id') && $this->input('area_id') === '') {
            $this->merge(['area_id' => null]);
        }
    }
}
