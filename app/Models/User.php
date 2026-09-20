<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        | Profielfoto
        |--------------------------------------------------------------------------
        */

        'profile_photo',

        /*
        |--------------------------------------------------------------------------
        | Laatste loginmethode
        |--------------------------------------------------------------------------
        |
        | Mogelijke waarden:
        |
        | - password
        | - email_code
        | - magic_link
        | - google
        | - github
        | - facebook
        | - tiktok
        |
        */

        'login_provider',

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
        | TikTok OAuth
        |--------------------------------------------------------------------------
        */

        'tiktok_id',
        'tiktok_avatar',

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

    /*
    |--------------------------------------------------------------------------
    | Mashal Studio afbeeldingen
    |--------------------------------------------------------------------------
    */

    /**
     * Alle originele afbeeldingsprojecten van deze gebruiker.
     */
    public function images(): HasMany
    {
        return $this->hasMany(
            Image::class
        );
    }

    /**
     * Alle gegenereerde afbeeldingsversies van deze gebruiker.
     */
    public function imageVersions(): HasMany
    {
        return $this->hasMany(
            ImageVersion::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Account helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Controleer of deze gebruiker administrator is.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Controleer of het e-mailadres is geverifieerd.
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Profielfoto helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Controleer of de gebruiker zelf een profielfoto heeft geüpload.
     */
    public function hasProfilePhoto(): bool
    {
        $photo = trim(
            (string) $this->profile_photo
        );

        if ($photo === '') {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Externe profielfoto
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $photo,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Lokale profielfoto
        |--------------------------------------------------------------------------
        |
        | Alleen true teruggeven als het bestand daadwerkelijk nog bestaat.
        | Hierdoor wordt een oud databasepad nooit meer als geldige foto
        | weergegeven wanneer Railway het bestand niet meer heeft.
        |
        */

        $photo = ltrim(
            $photo,
            '/'
        );

        return Storage::disk('public')->exists(
            $photo
        );
    }

    /**
     * Geef de URL van de zelf geüploade profielfoto terug.
     */
    public function profilePhotoUrl(): ?string
    {
        $photo = trim(
            (string) $this->profile_photo
        );

        if ($photo === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Externe URL
        |--------------------------------------------------------------------------
        |
        | Als profile_photo ooit een volledige externe URL bevat,
        | geven we die direct terug.
        |
        */

        if (
            Str::startsWith(
                $photo,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $photo;
        }

        /*
        |--------------------------------------------------------------------------
        | Lokale publieke storage
        |--------------------------------------------------------------------------
        |
        | Database:
        |
        | profile-photos/abc123.jpg
        |
        | Browser:
        |
        | /storage/profile-photos/abc123.jpg
        |
        */

        $photo = ltrim(
            $photo,
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Bestand moet echt bestaan
        |--------------------------------------------------------------------------
        |
        | Op Railway kan een lokaal bestand verdwijnen wanneer geen persistent
        | Volume is gekoppeld. In dat geval geven we null terug zodat de site
        | automatisch terugvalt op een social avatar of initialen in plaats
        | van een kapotte afbeelding te tonen.
        |
        */

        if (
            ! Storage::disk('public')->exists(
                $photo
            )
        ) {
            return null;
        }

        return asset(
            'storage/' . $photo
        );
    }

    /**
     * Geef de beste beschikbare profielfoto terug.
     *
     * Voorkeursvolgorde:
     *
     * 1. Zelf geüploade profielfoto
     * 2. Google-avatar
     * 3. GitHub-avatar
     * 4. Facebook-avatar
     * 5. TikTok-avatar
     * 6. Geen afbeelding
     */
    public function avatarUrl(): ?string
    {
        $profilePhoto = $this->profilePhotoUrl();

        if ($profilePhoto !== null) {
            return $profilePhoto;
        }

        return $this->socialAvatar();
    }

    /**
     * Alias voor gebruik in Blade.
     */
    public function displayAvatar(): ?string
    {
        return $this->avatarUrl();
    }

    /**
     * Initialen gebruiken als er geen afbeelding beschikbaar is.
     *
     * Voorbeelden:
     *
     * Mashal Hussain -> MH
     * Mashal -> M
     */
    public function initials(): string
    {
        $name = trim(
            (string) $this->name
        );

        if ($name === '') {
            return 'U';
        }

        $parts = preg_split(
            '/\s+/',
            $name
        );

        if (
            ! is_array($parts) ||
            count($parts) === 0
        ) {
            return strtoupper(
                mb_substr(
                    $name,
                    0,
                    1
                )
            );
        }

        $first = trim(
            (string) ($parts[0] ?? '')
        );

        $last = '';

        if (count($parts) > 1) {
            $last = trim(
                (string) $parts[
                    count($parts) - 1
                ]
            );
        }

        $initials = '';

        if ($first !== '') {
            $initials .= mb_substr(
                $first,
                0,
                1
            );
        }

        if ($last !== '') {
            $initials .= mb_substr(
                $last,
                0,
                1
            );
        }

        $initials = strtoupper(
            $initials
        );

        if ($initials === '') {
            return 'U';
        }

        return $initials;
    }

    /*
    |--------------------------------------------------------------------------
    | OAuth helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Controleer of een Google-account gekoppeld is.
     */
    public function hasGoogleAccount(): bool
    {
        return trim(
            (string) $this->google_id
        ) !== '';
    }

    /**
     * Controleer of een GitHub-account gekoppeld is.
     */
    public function hasGitHubAccount(): bool
    {
        return trim(
            (string) $this->github_id
        ) !== '';
    }

    /**
     * Controleer of een Facebook-account gekoppeld is.
     */
    public function hasFacebookAccount(): bool
    {
        return trim(
            (string) $this->facebook_id
        ) !== '';
    }

    /**
     * Controleer of een TikTok-account gekoppeld is.
     */
    public function hasTikTokAccount(): bool
    {
        return trim(
            (string) $this->tiktok_id
        ) !== '';
    }

    /**
     * Controleer of minimaal één OAuth-account gekoppeld is.
     */
    public function hasSocialAccount(): bool
    {
        return $this->hasGoogleAccount()
            || $this->hasGitHubAccount()
            || $this->hasFacebookAccount()
            || $this->hasTikTokAccount();
    }

    /**
     * Geef de beste beschikbare OAuth-avatar terug.
     *
     * Voorkeursvolgorde:
     *
     * 1. Google
     * 2. GitHub
     * 3. Facebook
     * 4. TikTok
     */
    public function socialAvatar(): ?string
    {
        $googleAvatar = trim(
            (string) $this->google_avatar
        );

        if ($googleAvatar !== '') {
            return $googleAvatar;
        }

        $githubAvatar = trim(
            (string) $this->github_avatar
        );

        if ($githubAvatar !== '') {
            return $githubAvatar;
        }

        $facebookAvatar = trim(
            (string) $this->facebook_avatar
        );

        if ($facebookAvatar !== '') {
            return $facebookAvatar;
        }

        $tiktokAvatar = trim(
            (string) $this->tiktok_avatar
        );

        if ($tiktokAvatar !== '') {
            return $tiktokAvatar;
        }

        return null;
    }

    /**
     * Geef de eerste gekoppelde OAuth-provider terug.
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

        if ($this->hasTikTokAccount()) {
            return 'tiktok';
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

        if ($this->hasTikTokAccount()) {
            $providers[] = 'tiktok';
        }

        return $providers;
    }

    /*
    |--------------------------------------------------------------------------
    | Login provider helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Laatste gebruikte loginmethode.
     */
    public function loginProvider(): string
    {
        $provider = strtolower(
            trim(
                (string) $this->login_provider
            )
        );

        $allowedProviders = [
            'password',
            'email_code',
            'magic_link',
            'google',
            'github',
            'facebook',
            'tiktok',
        ];

        if (
            in_array(
                $provider,
                $allowedProviders,
                true
            )
        ) {
            return $provider;
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback voor oudere gebruikers
        |--------------------------------------------------------------------------
        */

        if ($this->hasGoogleAccount()) {
            return 'google';
        }

        if ($this->hasGitHubAccount()) {
            return 'github';
        }

        if ($this->hasFacebookAccount()) {
            return 'facebook';
        }

        if ($this->hasTikTokAccount()) {
            return 'tiktok';
        }

        return 'password';
    }

    /**
     * Menselijke naam van de laatste loginmethode.
     */
    public function loginProviderLabel(): string
    {
        $provider = $this->loginProvider();

        return match ($provider) {
            'google' => 'Google',
            'github' => 'GitHub',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            'email_code' => 'E-mailcode',
            'magic_link' => 'Magic link',
            'password' => 'Wachtwoord',
            default => 'Onbekend',
        };
    }

    /**
     * Korte icon-code voor dashboard/Blade.
     */
    public function loginProviderIcon(): string
    {
        $provider = $this->loginProvider();

        return match ($provider) {
            'google' => 'google',
            'github' => 'github',
            'facebook' => 'facebook',
            'tiktok' => 'tiktok',
            'email_code' => 'email',
            'magic_link' => 'link',
            'password' => 'lock',
            default => 'user',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Login provider controles
    |--------------------------------------------------------------------------
    */

    /**
     * Laatste login via Google.
     */
    public function loggedInWithGoogle(): bool
    {
        return $this->loginProvider() === 'google';
    }

    /**
     * Laatste login via GitHub.
     */
    public function loggedInWithGitHub(): bool
    {
        return $this->loginProvider() === 'github';
    }

    /**
     * Laatste login via Facebook.
     */
    public function loggedInWithFacebook(): bool
    {
        return $this->loginProvider() === 'facebook';
    }

    /**
     * Laatste login via TikTok.
     */
    public function loggedInWithTikTok(): bool
    {
        return $this->loginProvider() === 'tiktok';
    }

    /**
     * Laatste login via e-mailcode.
     */
    public function loggedInWithEmailCode(): bool
    {
        return $this->loginProvider() === 'email_code';
    }

    /**
     * Laatste login via magic link.
     */
    public function loggedInWithMagicLink(): bool
    {
        return $this->loginProvider() === 'magic_link';
    }

    /**
     * Laatste login via wachtwoord.
     */
    public function loggedInWithPassword(): bool
    {
        return $this->loginProvider() === 'password';
    }
}