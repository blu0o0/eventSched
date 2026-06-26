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

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return Storage::url($this->photo);
        }
        return null;
    }
}