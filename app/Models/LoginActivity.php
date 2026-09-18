<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    /**
     * Velden die door de login-security service
     * via LoginActivity::create() mogen worden opgeslagen.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'guard',
        'login_provider',

        'ip_address',

        'city',
        'region',
        'country',
        'country_code',
        'timezone',
        'location_source',

        'device',
        'device_type',
        'browser',
        'operating_system',
        'user_agent',
        'device_fingerprint',

        'is_new_device',
        'remember',

        'logged_in_at',
    ];

    /**
     * Typecasts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_new_device' => 'boolean',
            'remember' => 'boolean',
            'logged_in_at' => 'datetime',
        ];
    }

    /**
     * Gebruiker bij deze loginactiviteit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Leesbare naam van de loginmethode.
     */
    public function providerLabel(): string
    {
        return match ($this->login_provider) {
            'password' => 'E-mail + wachtwoord',
            'email_code' => 'E-mailcode',
            'magic_link' => 'Magic link',
            'google' => 'Google',
            'github' => 'GitHub',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',

            default => 'Onbekend',
        };
    }

    /**
     * Leesbare locatie.
     */
    public function locationLabel(): string
    {
        if ($this->location_source === 'local') {
            return 'Lokale ontwikkeling';
        }

        $parts = array_values(
            array_filter([
                trim((string) $this->city),
                trim((string) $this->country),
            ])
        );

        if ($parts === []) {
            return 'Onbekende locatie';
        }

        return implode(', ', $parts);
    }

    /**
     * Leesbaar apparaat.
     */
    public function deviceLabel(): string
    {
        $device = trim(
            (string) $this->device
        );

        if ($device !== '') {
            return $device;
        }

        return match ($this->device_type) {
            'mobile' => 'Mobiel apparaat',
            'tablet' => 'Tablet',
            'desktop' => 'Computer',
            'bot' => 'Automatisch systeem',

            default => 'Onbekend apparaat',
        };
    }
}