<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'venue_id',
        'date',
        'start_time',
        'end_time',
        'capacity',
        'status',
        'user_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'postponement_reason',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    /**
     * Get the venue for this reservation
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the user who created this reservation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this reservation
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if reservation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if reservation is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if reservation is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if reservation is postponed
     */
    public function isPostponed(): bool
    {
        return $this->status === 'postponed';
    }

    /**
     * Check if reservation overlaps with another reservation
     */
    public function overlapsWith(Reservation $other): bool
    {
        if ($this->venue_id !== $other->venue_id || $this->date != $other->date) {
            return false;
        }

        // Format date properly to avoid time component issues
        $thisDateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : (string) $this->date;
        $otherDateStr = $other->date instanceof Carbon ? $other->date->format('Y-m-d') : (string) $other->date;
        
        $thisStart = Carbon::parse($thisDateStr . ' ' . $this->start_time);
        $thisEnd = Carbon::parse($thisDateStr . ' ' . $this->end_time);
        $otherStart = Carbon::parse($otherDateStr . ' ' . $other->start_time);
        $otherEnd = Carbon::parse($otherDateStr . ' ' . $other->end_time);

        return $thisStart < $otherEnd && $thisEnd > $otherStart;
    }
}
