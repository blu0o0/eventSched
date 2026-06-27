<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'venue_id',
        'photo',
        'status',
        'map_coordinates',
    ];

    protected $attributes = [
        'status' => 'available',
    ];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [];
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'area_id');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Use asset() helper for public URL
            return asset('storage/' . $this->photo);
        }
        return null;
    }
}