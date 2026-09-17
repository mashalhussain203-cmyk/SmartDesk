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
        | Facebook OAuth
        |--------------------------------------------------------------------------
        */

        'facebook_id',
        'facebook_avatar',

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
     * Controleer of een Google-account gekoppeld is.
     */
    public function hasGoogleAccount(): bool
    {
        return filled($this->google_id);
    }

    /**
     * Controleer of een GitHub-account gekoppeld is.
     */
    public function hasGitHubAccount(): bool
    {
        return filled($this->github_id);
    }

    /**
     * Controleer of een Facebook-account gekoppeld is.
     */
    public function hasFacebookAccount(): bool
    {
        return filled($this->facebook_id);
    }

    /**
     * Controleer of minimaal één externe OAuth-provider
     * aan dit Mashal-account gekoppeld is.
     */
    public function hasSocialAccount(): bool
    {
        return $this->hasGoogleAccount()
            || $this->hasGitHubAccount()
            || $this->hasFacebookAccount();
    }

    /**
     * Geef de beste beschikbare profielfoto terug.
     *
     * Voorkeursvolgorde:
     *
     * 1. Google
     * 2. GitHub
     * 3. Facebook
     * 4. Geen profielfoto
     */
    public function socialAvatar(): ?string
    {
        if (filled($this->google_avatar)) {
            return $this->google_avatar;
        }

        if (filled($this->github_avatar)) {
            return $this->github_avatar;
        }

        if (filled($this->facebook_avatar)) {
            return $this->facebook_avatar;
        }

        return null;
    }

    /**
     * Geef de naam van de eerste gekoppelde OAuth-provider terug.
     */
    public function socialProvider(): ?string
    {
        if ($this->hasGoogleAccount()) {
            return 'google';
        }

        if ($this->hasGitHubAccount()) {
            return 'github';
        }

        if ($this->hasFacebookAccount()) {
            return 'facebook';
        }

        return null;
    }

    /**
     * Geef alle gekoppelde OAuth-providers terug.
     *
     * @return array<int, string>
     */
    public function socialProviders(): array
    {
        $providers = [];

        if ($this->hasGoogleAccount()) {
            $providers[] = 'google';
        }

        if ($this->hasGitHubAccount()) {
            $providers[] = 'github';
        }

        if ($this->hasFacebookAccount()) {
            $providers[] = 'facebook';
        }

        return $providers;
    }
}