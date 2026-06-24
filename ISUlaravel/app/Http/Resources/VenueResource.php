<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'description' => $this->description,
            'map_coordinates' => $this->map_coordinates,
            'photo_url' => $this->photo_url,
            'status' => $this->status,
            'unavailable_until' => $this->unavailable_until?->format('Y-m-d'),
            'days_until_available' => $this->days_until_available,
            'is_available' => $this->isAvailable(),
            'is_unavailable' => $this->isUnavailable(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
