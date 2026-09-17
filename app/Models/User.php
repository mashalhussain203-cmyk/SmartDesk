<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * Velden die via mass assignment mogen worden opgeslagen.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'google_avatar',
        'password',
        'is_admin',
        'email_verified_at',
    ];

    /**
     * Velden die verborgen blijven bij arrays en JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Type casting voor databasevelden.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Controleer of deze gebruiker administrator is.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Controleer of het e-mailadres is geverifieerd.
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Controleer of er een Google-account gekoppeld is.
     */
    public function hasGoogleAccount(): bool
    {
        return !empty($this->google_id);
    }
}