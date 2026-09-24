<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class LoginActivity extends Model
{
    /**
     * Velden die door de login-security service
     * via LoginActivity::create() en update()
     * mogen worden opgeslagen.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Gebruiker / authenticatie
        |--------------------------------------------------------------------------
        */

        'user_id',
        'guard',
        'login_provider',


        /*
        |--------------------------------------------------------------------------
        | IP-adres
        |--------------------------------------------------------------------------
        */

        'ip_address',


        /*
        |--------------------------------------------------------------------------
        | Geschatte IP-locatie
        |--------------------------------------------------------------------------
        |
        | Deze gegevens komen bijvoorbeeld van ipapi.co.
        |
        */

        'city',
        'region',
        'country',
        'country_code',
        'timezone',
        'location_source',


        /*
        |--------------------------------------------------------------------------
        | Precieze browserlocatie
        |--------------------------------------------------------------------------
        |
        | Alleen opslaan wanneer de gebruiker in de browser
        | toestemming heeft gegeven.
        |
        */

        'latitude',
        'longitude',
        'location_accuracy',
        'location_permission',
        'precise_location_captured_at',


        /*
        |--------------------------------------------------------------------------
        | Browser timezone
        |--------------------------------------------------------------------------
        |
        | Bijvoorbeeld:
        |
        | Europe/Amsterdam
        | Europe/London
        | America/New_York
        |
        */

        'browser_timezone',


        /*
        |--------------------------------------------------------------------------
        | Apparaat
        |--------------------------------------------------------------------------
        */

        'device',
        'device_type',
        'browser',
        'operating_system',
        'user_agent',
        'device_fingerprint',


        /*
        |--------------------------------------------------------------------------
        | Security status
        |--------------------------------------------------------------------------
        */

        'is_new_device',
        'remember',


        /*
        |--------------------------------------------------------------------------
        | Beveiligingsmail
        |--------------------------------------------------------------------------
        */

        'notification_sent_at',
        'notification_failed_at',


        /*
        |--------------------------------------------------------------------------
        | Login tijd
        |--------------------------------------------------------------------------
        */

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

            /*
            |--------------------------------------------------------------------------
            | IDs / booleans
            |--------------------------------------------------------------------------
            */

            'user_id' => 'integer',

            'is_new_device' => 'boolean',

            'remember' => 'boolean',


            /*
            |--------------------------------------------------------------------------
            | GPS
            |--------------------------------------------------------------------------
            */

            'latitude' => 'float',

            'longitude' => 'float',

            'location_accuracy' => 'float',


            /*
            |--------------------------------------------------------------------------
            | Datums
            |--------------------------------------------------------------------------
            */

            'logged_in_at' => 'datetime',

            'precise_location_captured_at' => 'datetime',

            'notification_sent_at' => 'datetime',

            'notification_failed_at' => 'datetime',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relaties
    |--------------------------------------------------------------------------
    */

    /**
     * De gebruiker bij deze loginactiviteit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Login provider
    |--------------------------------------------------------------------------
    */

    /**
     * Leesbare naam van de gebruikte loginmethode.
     */
    public function providerLabel(): string
    {
        return match (
            strtolower(
                trim(
                    (string) $this->login_provider
                )
            )
        ) {
            'password' => 'E-mail + wachtwoord',

            'email_code' => 'E-mailcode',

            'magic_link' => 'Magic link',

            'google' => 'Google',

            'github' => 'GitHub',

            'facebook' => 'Facebook',

            'linkedin' => 'LinkedIn',

            'tiktok' => 'TikTok',

            default => 'Onbekend',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Locatie
    |--------------------------------------------------------------------------
    */

    /**
     * Controleer of een precieze browserlocatie beschikbaar is.
     */
    public function hasPreciseLocation(): bool
    {
        return
            $this->latitude !== null &&
            $this->longitude !== null;
    }


    /**
     * Controleer of de gebruiker browserlocatie heeft toegestaan.
     */
    public function hasLocationPermission(): bool
    {
        return strtolower(
            trim(
                (string) $this->location_permission
            )
        ) === 'granted';
    }


    /**
     * Leesbare geschatte locatie.
     *
     * Dit gebruikt de stad/regio/land die via
     * IP-geolocatie of reverse-geocoding zijn opgeslagen.
     */
    public function locationLabel(): string
    {
        /*
        |--------------------------------------------------------------------------
        | Lokale ontwikkeling
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(
                trim(
                    (string) $this->location_source
                )
            ) === 'local'
        ) {
            return 'Lokale ontwikkeling';
        }


        /*
        |--------------------------------------------------------------------------
        | Locatie onderdelen
        |--------------------------------------------------------------------------
        */

        $parts = array_values(
            array_filter(
                [
                    $this->cleanLocationValue(
                        $this->city
                    ),

                    $this->cleanLocationValue(
                        $this->region
                    ),

                    $this->cleanLocationValue(
                        $this->country
                    ),
                ],
                static fn (?string $value): bool =>
                    $value !== null &&
                    $value !== ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Geen locatie bekend
        |--------------------------------------------------------------------------
        */

        if ($parts === []) {
            return 'Onbekende locatie';
        }


        /*
        |--------------------------------------------------------------------------
        | Dubbele waarden verwijderen
        |--------------------------------------------------------------------------
        */

        $parts = array_values(
            array_unique(
                $parts
            )
        );

        return implode(
            ', ',
            $parts
        );
    }


    /**
     * Geef GPS-coördinaten als leesbare tekst terug.
     */
    public function coordinatesLabel(): ?string
    {
        if (! $this->hasPreciseLocation()) {
            return null;
        }

        return number_format(
            (float) $this->latitude,
            6,
            '.',
            ''
        )
            . ', '
            . number_format(
                (float) $this->longitude,
                6,
                '.',
                ''
            );
    }


    /**
     * Leesbare GPS-nauwkeurigheid.
     *
     * Bijvoorbeeld:
     *
     * ± 18 meter
     */
    public function accuracyLabel(): ?string
    {
        if ($this->location_accuracy === null) {
            return null;
        }

        $accuracy = max(
            0,
            (float) $this->location_accuracy
        );

        return '± '
            . number_format(
                $accuracy,
                0,
                ',',
                '.'
            )
            . ' meter';
    }


    /**
     * Leesbare bron van de locatie.
     */
    public function locationSourceLabel(): string
    {
        return match (
            strtolower(
                trim(
                    (string) $this->location_source
                )
            )
        ) {
            'browser_gps' => 'Browserlocatie',

            'gps' => 'Browserlocatie',

            'ipapi' => 'IP-geolocatie',

            'ip' => 'IP-geolocatie',

            'local' => 'Lokale ontwikkeling',

            'disabled' => 'Locatie uitgeschakeld',

            'unavailable' => 'Locatie niet beschikbaar',

            default => 'Onbekende locatiebron',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Apparaat
    |--------------------------------------------------------------------------
    */

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

        return match (
            strtolower(
                trim(
                    (string) $this->device_type
                )
            )
        ) {
            'mobile' => 'Mobiel apparaat',

            'tablet' => 'Tablet',

            'desktop' => 'Computer',

            'bot' => 'Automatisch systeem',

            default => 'Onbekend apparaat',
        };
    }


    /**
     * Leesbaar type apparaat.
     */
    public function deviceTypeLabel(): string
    {
        return match (
            strtolower(
                trim(
                    (string) $this->device_type
                )
            )
        ) {
            'mobile' => 'Mobiel',

            'tablet' => 'Tablet',

            'desktop' => 'Computer',

            'bot' => 'Automatisch systeem',

            default => 'Onbekend',
        };
    }


    /**
     * Leesbare browser.
     */
    public function browserLabel(): string
    {
        $browser = trim(
            (string) $this->browser
        );

        return $browser !== ''
            ? $browser
            : 'Onbekende browser';
    }


    /**
     * Leesbaar besturingssysteem.
     */
    public function operatingSystemLabel(): string
    {
        $operatingSystem = trim(
            (string) $this->operating_system
        );

        return $operatingSystem !== ''
            ? $operatingSystem
            : 'Onbekend besturingssysteem';
    }


    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    */

    /**
     * Bepaal de beste timezone voor deze login.
     *
     * Volgorde:
     *
     * 1. browser timezone
     * 2. timezone uit IP-locatie
     * 3. Laravel application timezone
     * 4. Europe/Amsterdam
     */
    public function effectiveTimezone(): string
    {
        $candidates = [
            trim(
                (string) $this->browser_timezone
            ),

            trim(
                (string) $this->timezone
            ),

            trim(
                (string) config(
                    'app.timezone',
                    'Europe/Amsterdam'
                )
            ),

            'Europe/Amsterdam',
        ];

        foreach ($candidates as $timezone) {
            if ($this->isValidTimezone($timezone)) {
                return $timezone;
            }
        }

        return 'Europe/Amsterdam';
    }


    /**
     * Geef de login-tijd terug in de timezone
     * van de gebruiker.
     */
    public function localLoggedInAt(): ?CarbonInterface
    {
        if (! $this->logged_in_at instanceof CarbonInterface) {
            return null;
        }

        try {
            return $this->logged_in_at
                ->copy()
                ->timezone(
                    $this->effectiveTimezone()
                );
        } catch (Throwable) {
            return $this->logged_in_at;
        }
    }


    /**
     * Leesbare lokale login-tijd.
     *
     * Bijvoorbeeld:
     *
     * 19-09-2026 00:56
     */
    public function localLoginTimeLabel(): string
    {
        $date = $this->localLoggedInAt();

        if (! $date) {
            return 'Onbekend tijdstip';
        }

        return $date->format(
            'd-m-Y H:i'
        );
    }


    /**
     * Leesbare timezone.
     */
    public function timezoneLabel(): string
    {
        return $this->effectiveTimezone();
    }


    /*
    |--------------------------------------------------------------------------
    | Nieuw apparaat
    |--------------------------------------------------------------------------
    */

    /**
     * Leesbare status voor nieuw apparaat.
     */
    public function newDeviceLabel(): string
    {
        return $this->is_new_device
            ? 'Nieuw apparaat'
            : 'Bekend apparaat';
    }


    /*
    |--------------------------------------------------------------------------
    | E-mail notificatie
    |--------------------------------------------------------------------------
    */

    /**
     * Controleer of de beveiligingsmail succesvol is verzonden.
     */
    public function notificationWasSent(): bool
    {
        return $this->notification_sent_at !== null;
    }


    /**
     * Controleer of het versturen van de beveiligingsmail is mislukt.
     */
    public function notificationFailed(): bool
    {
        return $this->notification_failed_at !== null;
    }


    /**
     * Leesbare status van de beveiligingsmail.
     */
    public function notificationStatusLabel(): string
    {
        if ($this->notificationWasSent()) {
            return 'Beveiligingsmail verzonden';
        }

        if ($this->notificationFailed()) {
            return 'Beveiligingsmail mislukt';
        }

        return 'Beveiligingsmail nog niet verzonden';
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Schoon een locatiewaarde op.
     */
    private function cleanLocationValue(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? $value
            : null;
    }


    /**
     * Controleer of een geldige PHP timezone is opgegeven.
     */
    private function isValidTimezone(
        string $timezone
    ): bool {
        if ($timezone === '') {
            return false;
        }

        try {
            new \DateTimeZone(
                $timezone
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}