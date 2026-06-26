<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'description',
        'map_coordinates',
        'photo',
        'status',
        'unavailable_until',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['photo_url', 'days_until_available'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unavailable_until' => 'date',
    ];
    }

    /**
     * Get reservations for this venue
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get approved reservations for this venue
     */
    public function approvedReservations()
    {
        return $this->hasMany(Reservation::class)->where('status', 'approved');
    }

    /**
     * Get areas for this venue
     */
    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Get the photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return Storage::url($this->photo);
        }
        return null;
    }

    /**
     * Check if venue is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if venue is damaged
     */
    public function isDamaged(): bool
    {
        return $this->status === 'damaged';
    }

    /**
     * Check if venue is under construction
     */
    public function isUnderConstruction(): bool
    {
        return $this->status === 'under_construction';
    }

    /**
     * Check if venue is unavailable (damaged or under construction)
     */
    public function isUnavailable(): bool
    {
        return in_array($this->status, ['damaged', 'under_construction']);
    }

    /**
     * Get days until venue is available again
     */
    public function getDaysUntilAvailableAttribute(): ?int
    {
        if (!$this->unavailable_until || $this->isAvailable()) {
            return null;
        }

        $now = now()->startOfDay();
        $unavailableUntil = \Carbon\Carbon::parse($this->unavailable_until)->startOfDay();
        
        if ($unavailableUntil->isPast()) {
            return 0; // Already past the date, should be available
        }

        return $now->diffInDays($unavailableUntil);
    }

    /**
     * Get upcoming approved reservations
     */
    public function upcomingReservations()
    {
        return $this->hasMany(Reservation::class)
            ->where('status', 'approved')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time');
    }
}