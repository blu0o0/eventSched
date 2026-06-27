<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_verified' => 'boolean',
        ];
    }

    /**
     * New role constants
     */
    const ROLE_ADMINISTRATOR = 'ADMINISTRATOR';
    const ROLE_SBO_BSIT_WMAD = 'SBO BSIT WMAD';
    const ROLE_SBO_BSIT_NETSEC = 'SBO BSIT NETSEC';
    const ROLE_SBO_BSA = 'SBO BSA';
    const ROLE_SBL_BSLEA = 'SBL BSLEA';
    const ROLE_SSC_OFFICER = 'SSC OFFICER';
    const ROLE_FACULTY_STAFF = 'FACULTY/STAFF';
    const ROLE_STUDENT = 'STUDENT';

    /**
     * Available roles for select inputs
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMINISTRATOR => 'Administrator',
            self::ROLE_SBO_BSIT_WMAD => 'SBO BSIT WMAD',
            self::ROLE_SBO_BSIT_NETSEC => 'SBO BSIT NETSEC',
            self::ROLE_SBO_BSA => 'SBO BSA',
            self::ROLE_SBL_BSLEA => 'SBL BSLEA',
            self::ROLE_SSC_OFFICER => 'SSC Officer',
            self::ROLE_FACULTY_STAFF => 'Faculty/Staff',
            self::ROLE_STUDENT => 'Student',
        ];
    }

    /**
     * Check if user is administrator
     */
    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    /**
     * Check if user is SBO BSIT WMAD
     */
    public function isSboBsitWmad(): bool
    {
        return $this->role === self::ROLE_SBO_BSIT_WMAD;
    }

    /**
     * Check if user is SBO BSIT NETSEC
     */
    public function isSboBsitNetsec(): bool
    {
        return $this->role === self::ROLE_SBO_BSIT_NETSEC;
    }

    /**
     * Check if user is SBO BSA
     */
    public function isSboBsa(): bool
    {
        return $this->role === self::ROLE_SBO_BSA;
    }

    /**
     * Check if user is SBL BSLEA
     */
    public function isSblBslea(): bool
    {
        return $this->role === self::ROLE_SBL_BSLEA;
    }

    /**
     * Check if user is SSC Officer (alias for backward compatibility)
     */
    public function isSscOfficer(): bool
    {
        return $this->role === self::ROLE_SSC_OFFICER;
    }

    /**
     * Check if user is OSAS (deprecated - use isSscOfficer instead)
     */
    public function isOsas(): bool
    {
        return $this->isSscOfficer();
    }

    /**
     * Check if user is Faculty/Staff
     */
    public function isFacultyStaff(): bool
    {
        return $this->role === self::ROLE_FACULTY_STAFF;
    }

    /**
     * Check if user is Student
     */
    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    /**
     * Get reservations created by this user
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get emergency reports created by this user
     */
    public function emergencyReports()
    {
        return $this->hasMany(EmergencyReport::class, 'reporter_id');
    }

    /**
     * Get reservations approved by this user
     */
    public function approvedReservations()
    {
        return $this->hasMany(Reservation::class, 'approved_by');
    }
}