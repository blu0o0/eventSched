<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReservationResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'venue_id' => $this->venue_id,
            'area_id' => $this->area_id,
            'area_name' => $this->area_name,
            'date' => $this->date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'postponement_reason' => $this->postponement_reason,
            'edited_at' => $this->edited_at,
            'event_approval_file' => $this->event_approval_file,
            'event_approval_file_url' => $this->event_approval_file ? Storage::url($this->event_approval_file) : null,
            
            // Relationships
            'venue' => new VenueResource($this->whenLoaded('venue')),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'approver' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                ];
            }),
            'area' => $this->whenLoaded('area'),
        ];
    }
}
