<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
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
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'required|integer|min:1',
            'end_date' => 'nullable|date|after_or_equal:date',
            'force' => 'boolean',
            'event_approval_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];
    }
}
