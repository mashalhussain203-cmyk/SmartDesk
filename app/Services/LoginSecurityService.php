<?php

namespace App\Services;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LoginSecurityService
{
    public function __construct(
        private readonly DeviceDetectionService $deviceDetection,
        private readonly IpLocationService $ipLocation,
        private readonly BrevoMailService $brevoMail
    ) {
    }

    /**
     * Registreer één succesvolle Laravel-login.
     *
     * Belangrijk:
     *
     * - Een fout in locatiebepaling mag de login nooit blokkeren.
     * - Een fout bij Brevo mag de login nooit blokkeren.
     * - GPS wordt alleen gebruikt wanneer de browser dit heeft aangeleverd.
     * - Browserlocatie vereist toestemming van de gebruiker.
     */
    public function recordLogin(
        Login $event,
        Request $request
    ): ?LoginActivity {
        if (! (bool) config('login-security.enabled', true)) {
            $this->forgetBrowserMetadata(
                $request
            );

            return null;
        }

        if (! $event->user instanceof User) {
            $this->forgetBrowserMetadata(
                $request
            );

            return null;
        }

        $user = $event->user;

        try {

        /*
        |--------------------------------------------------------------------------
        | Oude historie opruimen
        |--------------------------------------------------------------------------
        */

        $this->pruneOldActivities(
            $user->id
        );

        /*
        |--------------------------------------------------------------------------
        | User-Agent
        |--------------------------------------------------------------------------
        */

        $userAgent = trim(
            (string) $request->userAgent()
        );

        /*
        |--------------------------------------------------------------------------
        | Apparaat detecteren
        |--------------------------------------------------------------------------
        */

        $device = $this->deviceDetection->detect(
            $userAgent
        );

        /*
        |--------------------------------------------------------------------------
        | Publiek client-IP bepalen
        |--------------------------------------------------------------------------
        |
        | IpLocationService bepaalt het client-IP.
        |
        | Omdat Railway achter een reverse proxy zit, moet bootstrap/app.php
        | trustProxies() bevatten.
        |
        */

        $resolvedIpAddress = $this->ipLocation->resolveClientIp(
            $request
        );

        $ipAddress = (bool) config(
            'login-security.privacy.store_ip',
            true
        )
            ? $resolvedIpAddress
            : null;

        /*
        |--------------------------------------------------------------------------
        | Geschatte IP-locatie
        |--------------------------------------------------------------------------
        |
        | Voor de schatting mag het tijdelijk bepaalde client-IP worden gebruikt,
        | ook wanneer het IP-adres zelf niet in login_activities wordt opgeslagen.
        |
        */

        $location = (bool) config(
            'login-security.privacy.store_estimated_location',
            true
        )
            ? $this->safeLookupLocation(
                $resolvedIpAddress
            )
            : [
                'city' => null,
                'region' => null,
                'country' => null,
                'country_code' => null,
                'timezone' => null,
                'source' => 'privacy_disabled',
            ];

        /*
        |--------------------------------------------------------------------------
        | Loginmethode
        |--------------------------------------------------------------------------
        */

        $loginProvider = $this->resolveLoginProvider(
            $request,
            $user
        );

        /*
        |--------------------------------------------------------------------------
        | Browser security metadata
        |--------------------------------------------------------------------------
        |
        | Deze gegevens kunnen straks door JavaScript voorafgaand aan de login
        | worden aangeleverd.
        |
        | Ondersteunde informatie:
        |
        | - browser timezone
        | - latitude
        | - longitude
        | - GPS accuracy
        | - location permission
        |
        | Voor OAuth kunnen deze waarden eerst in de sessie worden opgeslagen.
        |
        */

        $browserMetadata = $this->resolveBrowserMetadata(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Nieuw apparaat detecteren
        |--------------------------------------------------------------------------
        */

        $hadPreviousActivity = LoginActivity::query()
            ->where('user_id', $user->id)
            ->exists();

        $fingerprint = trim(
            (string) ($device['fingerprint'] ?? '')
        );

        $knownFingerprint = false;

        if ($fingerprint !== '') {
            $knownFingerprint = LoginActivity::query()
                ->where('user_id', $user->id)
                ->where(
                    'device_fingerprint',
                    $fingerprint
                )
                ->exists();
        }

        /*
        |--------------------------------------------------------------------------
        | Eerste geregistreerde login
        |--------------------------------------------------------------------------
        |
        | De allereerste opgeslagen login wordt niet automatisch als
        | "nieuw apparaat" gemarkeerd.
        |
        */

        $isNewDevice =
            (bool) config(
                'login-security.new_device.enabled',
                true
            ) &&
            $hadPreviousActivity &&
            ! $knownFingerprint;

        /*
        |--------------------------------------------------------------------------
        | Basisgegevens voor LoginActivity
        |--------------------------------------------------------------------------
        */

        $data = [
            'user_id' => $user->id,

            'guard' => trim(
                (string) $event->guard
            ),

            'login_provider' => $loginProvider,

            'ip_address' => $ipAddress,

            'city' => $location['city'] ?? null,

            'region' => $location['region'] ?? null,

            'country' => $location['country'] ?? null,

            'country_code' => $location['country_code'] ?? null,

            'timezone' => $location['timezone'] ?? null,

            'location_source' => $location['source'] ?? 'unavailable',

            'device' => $device['device'] ?? 'Onbekend apparaat',

            'device_type' => $device['device_type'] ?? 'unknown',

            'browser' => $device['browser'] ?? 'Onbekende browser',

            'operating_system' =>
                $device['operating_system']
                ?? 'Onbekend besturingssysteem',

            'user_agent' => (
                (bool) config(
                    'login-security.privacy.store_user_agent',
                    true
                ) &&
                $userAgent !== ''
            )
                ? mb_substr(
                    $userAgent,
                    0,
                    4000
                )
                : null,

            'device_fingerprint' => $fingerprint !== ''
                ? $fingerprint
                : null,

            'is_new_device' => $isNewDevice,

            'remember' => (bool) $event->remember,

            /*
            |--------------------------------------------------------------------------
            | Tijdstip
            |--------------------------------------------------------------------------
            |
            | now() gebruikt de timezone uit config/app.php.
            |
            | Met:
            |
            | APP_TIMEZONE=Europe/Amsterdam
            |
            | wordt bijvoorbeeld 00:56 correct opgeslagen/getoond.
            |
            */

            'logged_in_at' => now(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Nieuwe browser/GPS velden
        |--------------------------------------------------------------------------
        |
        | We voegen deze alleen toe wanneer de databasekolom daadwerkelijk
        | bestaat.
        |
        | Daardoor veroorzaakt deployment vóór de nieuwe migration geen 500.
        |
        */

        $this->addColumnIfAvailable(
            $data,
            'browser_timezone',
            $browserMetadata['browser_timezone']
        );

        $this->addColumnIfAvailable(
            $data,
            'latitude',
            $browserMetadata['latitude']
        );

        $this->addColumnIfAvailable(
            $data,
            'longitude',
            $browserMetadata['longitude']
        );

        $this->addColumnIfAvailable(
            $data,
            'location_accuracy',
            $browserMetadata['location_accuracy']
        );

        $this->addColumnIfAvailable(
            $data,
            'location_permission',
            $browserMetadata['location_permission']
        );

        if (
            $browserMetadata['latitude'] !== null &&
            $browserMetadata['longitude'] !== null
        ) {
            $this->addColumnIfAvailable(
                $data,
                'precise_location_captured_at',
                now()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Login opslaan
        |--------------------------------------------------------------------------
        */

        $activity = LoginActivity::create(
            $data
        );

        /*
        |--------------------------------------------------------------------------
        | Beveiligingsmail
        |--------------------------------------------------------------------------
        */

        if (
            (bool) config(
                'login-security.email.enabled',
                true
            )
        ) {
            $this->sendLoginEmail(
                $user,
                $activity
            );
        }

        return $activity->fresh();

        } finally {
            /*
            |--------------------------------------------------------------------------
            | Tijdelijke browsermetadata altijd verwijderen
            |--------------------------------------------------------------------------
            |
            | Ook wanneer een onverwachte fout optreedt tijdens het opslaan van
            | de loginactiviteit mag browser-/GPS-context niet blijven staan en
            | later aan een andere login worden gekoppeld.
            |
            */

            $this->forgetBrowserMetadata(
                $request
            );
        }
    }

    /**
     * IP-locatie veilig opzoeken.
     *
     * Een storing bij de externe provider mag de login nooit blokkeren.
     *
     * @return array{
     *     city: ?string,
     *     region: ?string,
     *     country: ?string,
     *     country_code: ?string,
     *     timezone: ?string,
     *     source: string
     * }
     */
    private function safeLookupLocation(
        ?string $ipAddress
    ): array {
        $fallback = [
            'city' => null,
            'region' => null,
            'country' => null,
            'country_code' => null,
            'timezone' => null,
            'source' => 'unavailable',
        ];

        if (! (bool) config(
            'login-security.geolocation.enabled',
            true
        )) {
            $fallback['source'] = 'disabled';

            return $fallback;
        }

        if (
            $ipAddress === null ||
            trim($ipAddress) === ''
        ) {
            return $fallback;
        }

        try {
            $location = $this->ipLocation->lookup(
                $ipAddress
            );

            if (! is_array($location)) {
                return $fallback;
            }

            return [
                'city' => $this->nullableString(
                    $location['city'] ?? null
                ),

                'region' => $this->nullableString(
                    $location['region'] ?? null
                ),

                'country' => $this->nullableString(
                    $location['country'] ?? null
                ),

                'country_code' => $this->nullableString(
                    $location['country_code'] ?? null
                ),

                'timezone' => $this->validTimezoneOrNull(
                    $location['timezone'] ?? null
                ),

                'source' => $this->nullableString(
                    $location['source'] ?? null
                ) ?? 'unavailable',
            ];
        } catch (Throwable $exception) {
            Log::warning(
                'Loginbeveiliging: IP-locatie kon niet worden bepaald.',
                [
                    'ip_address' => $ipAddress,
                    'message' => $exception->getMessage(),
                ]
            );

            return $fallback;
        }
    }

    /**
     * Browser-/GPS-metadata verzamelen.
     *
     * Volgorde:
     *
     * 1. huidige request
     * 2. sessie
     * 3. veilige fallback
     *
     * @return array{
     *     browser_timezone: ?string,
     *     latitude: ?float,
     *     longitude: ?float,
     *     location_accuracy: ?float,
     *     location_permission: string
     * }
     */
    private function resolveBrowserMetadata(
        Request $request
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Browser timezone
        |--------------------------------------------------------------------------
        */

        $browserTimezone = $this->firstNonEmptyString([
            $request->input('browser_timezone'),

            $request->header('X-Browser-Timezone'),

            $this->sessionValue(
                $request,
                'login_security.browser_timezone'
            ),
        ]);

        $browserTimezone = $this->validTimezoneOrNull(
            $browserTimezone
        );

        /*
        |--------------------------------------------------------------------------
        | Latitude
        |--------------------------------------------------------------------------
        */

        $latitude = $this->firstValidFloat([
            $request->input('latitude'),

            $request->input('login_latitude'),

            $this->sessionValue(
                $request,
                'login_security.latitude'
            ),
        ]);

        if (
            $latitude !== null &&
            (
                $latitude < -90 ||
                $latitude > 90
            )
        ) {
            $latitude = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Longitude
        |--------------------------------------------------------------------------
        */

        $longitude = $this->firstValidFloat([
            $request->input('longitude'),

            $request->input('login_longitude'),

            $this->sessionValue(
                $request,
                'login_security.longitude'
            ),
        ]);

        if (
            $longitude !== null &&
            (
                $longitude < -180 ||
                $longitude > 180
            )
        ) {
            $longitude = null;
        }

        /*
        |--------------------------------------------------------------------------
        | GPS accuracy
        |--------------------------------------------------------------------------
        */

        $accuracy = $this->firstValidFloat([
            $request->input('location_accuracy'),

            $request->input('gps_accuracy'),

            $this->sessionValue(
                $request,
                'login_security.location_accuracy'
            ),
        ]);

        if (
            $accuracy !== null &&
            $accuracy < 0
        ) {
            $accuracy = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Locatietoestemming
        |--------------------------------------------------------------------------
        */

        $permission = strtolower(
            trim(
                (string) $this->firstNonEmptyString([
                    $request->input('location_permission'),

                    $this->sessionValue(
                        $request,
                        'login_security.location_permission'
                    ),
                ])
            )
        );

        if (
            ! in_array(
                $permission,
                [
                    'granted',
                    'denied',
                    'prompt',
                    'unavailable',
                    'unsupported',
                ],
                true
            )
        ) {
            $permission = 'unknown';
        }

        /*
        |--------------------------------------------------------------------------
        | Privacycontrole
        |--------------------------------------------------------------------------
        |
        | Geen GPS opslaan wanneer:
        |
        | - precieze locatie is uitgeschakeld;
        | - opslag is uitgeschakeld;
        | - toestemming niet "granted" is.
        |
        */

        $preciseLocationEnabled = (bool) config(
            'login-security.precise_location.enabled',
            true
        );

        $storePreciseLocation = (bool) config(
            'login-security.privacy.store_precise_location_with_consent',
            true
        );

        if (
            ! $preciseLocationEnabled ||
            ! $storePreciseLocation ||
            $permission !== 'granted'
        ) {
            $latitude = null;
            $longitude = null;
            $accuracy = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Beide coördinaten moeten aanwezig zijn
        |--------------------------------------------------------------------------
        */

        if (
            $latitude === null ||
            $longitude === null
        ) {
            $latitude = null;
            $longitude = null;
            $accuracy = null;
        }

        return [
            'browser_timezone' => $browserTimezone,

            'latitude' => $latitude,

            'longitude' => $longitude,

            'location_accuracy' => $accuracy,

            'location_permission' => $permission,
        ];
    }

    /**
     * Loginmethode bepalen.
     */
    private function resolveLoginProvider(
        Request $request,
        User $user
    ): string {
        $routeName = trim(
            (string) optional(
                $request->route()
            )->getName()
        );

        $routeProviders = [
            'login.submit' => 'password',

            'register.submit' => 'password',

            'email-login.verify' => 'email_code',

            'email-login.link.verify' => 'magic_link',

            'email-login.link.complete' => 'magic_link',

            'google.callback' => 'google',

            'github.callback' => 'github',

            'facebook.callback' => 'facebook',

            'tiktok.callback' => 'tiktok',

            'tiktok.complete.submit' => 'tiktok',
        ];

        if (
            $routeName !== '' &&
            isset($routeProviders[$routeName])
        ) {
            return $routeProviders[$routeName];
        }

        /*
        |--------------------------------------------------------------------------
        | Eventueel expliciet meegegeven provider
        |--------------------------------------------------------------------------
        */

        $requestProvider = strtolower(
            trim(
                (string) $request->input(
                    'login_provider',
                    ''
                )
            )
        );

        $allowedProviders = [
            'passkey',
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
                $requestProvider,
                $allowedProviders,
                true
            )
        ) {
            return $requestProvider;
        }

        /*
        |--------------------------------------------------------------------------
        | Database provider
        |--------------------------------------------------------------------------
        */

        $storedProvider = strtolower(
            trim(
                (string) $user->login_provider
            )
        );

        if (
            in_array(
                $storedProvider,
                $allowedProviders,
                true
            )
        ) {
            return $storedProvider;
        }

        return 'unknown';
    }

    /**
     * Beveiligingsmail via Brevo versturen.
     */
    private function sendLoginEmail(
        User $user,
        LoginActivity $activity
    ): void {
        if (
            trim(
                (string) $user->email
            ) === ''
        ) {
            return;
        }

        $subject = $activity->is_new_device
            ? 'Nieuw apparaat ingelogd op je Mashal-account'
            : 'Nieuwe login op je Mashal Automotive-account';

        try {
            $this->brevoMail->send(
                (string) $user->email,

                (string) $user->name,

                $subject,

                'emails.login-alert',

                [
                    'user' => $user,

                    'activity' => $activity,

                    'providerLabel' =>
                        $activity->providerLabel(),

                    'locationLabel' =>
                        $activity->locationLabel(),

                    'displayTime' =>
                        $activity->localLoginTimeLabel(),

                    'securityUrl' =>
                        route('security.index'),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Mail succesvol
            |--------------------------------------------------------------------------
            */

            if (
                $this->loginActivityColumnExists(
                    'notification_sent_at'
                )
            ) {
                $activity->forceFill([
                    'notification_sent_at' => now(),
                    'notification_failed_at' => null,
                ])->save();
            }
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Login nooit blokkeren vanwege e-mailfout
            |--------------------------------------------------------------------------
            */

            if (
                $this->loginActivityColumnExists(
                    'notification_failed_at'
                )
            ) {
                try {
                    $activity->forceFill([
                        'notification_failed_at' => now(),
                    ])->save();
                } catch (Throwable) {
                    //
                }
            }

            Log::error(
                'Loginbeveiligingsmail kon niet via Brevo worden verzonden.',
                [
                    'user_id' => $user->id,

                    'login_activity_id' =>
                        $activity->id,

                    'message' =>
                        $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * Oude loginactiviteiten verwijderen.
     */
    private function pruneOldActivities(
        int $userId
    ): void {
        $retentionDays = max(
            7,
            (int) config(
                'login-security.retention_days',
                90
            )
        );

        LoginActivity::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'logged_in_at',
                '<',
                now()->subDays(
                    $retentionDays
                )
            )
            ->delete();
    }

    /**
     * Voeg een waarde alleen toe wanneer de kolom daadwerkelijk bestaat.
     *
     * Hierdoor blijft deployment veilig als de code eerder live komt dan
     * de nieuwe database-migration.
     *
     * @param array<string, mixed> $data
     */
    private function addColumnIfAvailable(
        array &$data,
        string $column,
        mixed $value
    ): void {
        if (
            $this->loginActivityColumnExists(
                $column
            )
        ) {
            $data[$column] = $value;
        }
    }

    /**
     * Controleer of een login_activities-kolom bestaat.
     */
    private function loginActivityColumnExists(
        string $column
    ): bool {
        try {
            return Schema::hasColumn(
                'login_activities',
                $column
            );
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Lees veilig een sessiewaarde.
     */
    private function sessionValue(
        Request $request,
        string $key
    ): mixed {
        try {
            if (! $request->hasSession()) {
                return null;
            }

            return $request->session()->get(
                $key
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Tijdelijke browsermetadata opruimen.
     */
    private function forgetBrowserMetadata(
        Request $request
    ): void {
        try {
            if (! $request->hasSession()) {
                return;
            }

            $request->session()->forget([
                'login_security.browser_timezone',

                'login_security.latitude',

                'login_security.longitude',

                'login_security.location_accuracy',

                'login_security.location_permission',

                'login_security.context_captured_at',
            ]);
        } catch (Throwable) {
            //
        }
    }

    /**
     * Eerste bruikbare string teruggeven.
     *
     * @param array<int, mixed> $values
     */
    private function firstNonEmptyString(
        array $values
    ): ?string {
        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $value = trim(
                (string) $value
            );

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Eerste geldige float teruggeven.
     *
     * @param array<int, mixed> $values
     */
    private function firstValidFloat(
        array $values
    ): ?float {
        foreach ($values as $value) {
            if (
                $value === null ||
                $value === ''
            ) {
                continue;
            }

            if (! is_numeric($value)) {
                continue;
            }

            $number = (float) $value;

            if (
                is_finite($number)
            ) {
                return $number;
            }
        }

        return null;
    }

    /**
     * Maak een nullable string.
     */
    private function nullableString(
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
     * Controleer een timezone.
     */
    private function validTimezoneOrNull(
        mixed $value
    ): ?string {
        $timezone = $this->nullableString(
            $value
        );

        if ($timezone === null) {
            return null;
        }

        try {
            new \DateTimeZone(
                $timezone
            );

            return $timezone;
        } catch (Throwable) {
            return null;
        }
    }
}