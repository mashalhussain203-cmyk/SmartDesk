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

        /*
        |--------------------------------------------------------------------------
        | Google OAuth
        |--------------------------------------------------------------------------
        */

        'google_id',
        'google_avatar',

        /*
        |--------------------------------------------------------------------------
        | GitHub OAuth
        |--------------------------------------------------------------------------
        */

        'github_id',
        'github_avatar',

        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        */

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

    /**
     * Controleer of er een GitHub-account gekoppeld is.
     */
    public function hasGitHubAccount(): bool
    {
        return !empty($this->github_id);
    }

    /**
     * Controleer of minimaal één externe OAuth-provider
     * aan dit Mashal-account gekoppeld is.
     */
    public function hasSocialAccount(): bool
    {
        return $this->hasGoogleAccount()
            || $this->hasGitHubAccount();
    }

    /**
     * Geef de beste beschikbare profielfoto terug.
     *
     * Voorkeursvolgorde:
     *
     * 1. Google avatar
     * 2. GitHub avatar
     * 3. Geen avatar
     */
    public function socialAvatar(): ?string
    {
        if (!empty($this->google_avatar)) {
            return $this->google_avatar;
        }

        if (!empty($this->github_avatar)) {
            return $this->github_avatar;
        }

        return null;
    }
}