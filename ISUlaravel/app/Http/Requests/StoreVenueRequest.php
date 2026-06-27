<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $venueId = $this->route('venue')?->id;
        
        return [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'map_coordinates' => 'nullable|string|max:255',
            'photo' => 'nullable|url', // Accepts photo URL from Google Places
            'status' => 'required|in:available,damaged,under_construction',
            'unavailable_until' => 'nullable|date|after_or_equal:today|required_if:status,damaged,under_construction',
        ];
    }
}
