<?php

namespace App\Services;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     * Verwerk één succesvolle Laravel-login.
     *
     * De database-opslag is onafhankelijk van de e-mail.
     * Als Brevo tijdelijk niet bereikbaar is, blijft de gebruiker gewoon
     * ingelogd en blijft de loginactiviteit wel opgeslagen.
     */
    public function recordLogin(
        Login $event,
        Request $request
    ): ?LoginActivity {
        if (! (bool) config('login-security.enabled', true)) {
            return null;
        }

        if (! $event->user instanceof User) {
            return null;
        }

        $user = $event->user;

        $this->pruneOldActivities(
            $user->id
        );

        $userAgent = trim(
            (string) $request->userAgent()
        );

        $device = $this->deviceDetection->detect(
            $userAgent
        );

        $ipAddress = $this->ipLocation->resolveClientIp(
            $request
        );

        $location = $this->ipLocation->lookup(
            $ipAddress
        );

        $loginProvider = $this->resolveLoginProvider(
            $request,
            $user
        );

        $hadPreviousActivity = LoginActivity::query()
            ->where('user_id', $user->id)
            ->exists();

        $knownFingerprint = LoginActivity::query()
            ->where('user_id', $user->id)
            ->where(
                'device_fingerprint',
                $device['fingerprint']
            )
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | Eerste bekende login is niet automatisch "verdacht"
        |--------------------------------------------------------------------------
        */

        $isNewDevice = $hadPreviousActivity &&
            ! $knownFingerprint;

        $activity = LoginActivity::create([
            'user_id' => $user->id,
            'guard' => $event->guard,
            'login_provider' => $loginProvider,
            'ip_address' => $ipAddress,
            'city' => $location['city'],
            'region' => $location['region'],
            'country' => $location['country'],
            'country_code' => $location['country_code'],
            'timezone' => $location['timezone'],
            'location_source' => $location['source'],
            'device' => $device['device'],
            'device_type' => $device['device_type'],
            'browser' => $device['browser'],
            'operating_system' => $device['operating_system'],
            'user_agent' => $userAgent !== ''
                ? mb_substr($userAgent, 0, 4000)
                : null,
            'device_fingerprint' => $device['fingerprint'],
            'is_new_device' => $isNewDevice,
            'remember' => (bool) $event->remember,
            'logged_in_at' => now(),
        ]);

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

        return $activity;
    }

    /**
     * Bepaal de loginmethode.
     *
     * Voor wachtwoord-login wordt het Laravel Login-event al tijdens
     * Auth::attempt() afgevuurd. Daarom gebruiken we eerst de huidige
     * routenaam; die is op dat moment betrouwbaarder dan login_provider
     * uit de database.
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

        $storedProvider = trim(
            strtolower(
                (string) $user->login_provider
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
                $storedProvider,
                $allowedProviders,
                true
            )
        ) {
            return $storedProvider;
        }

        return 'unknown';
    }

    private function sendLoginEmail(
        User $user,
        LoginActivity $activity
    ): void {
        if (
            trim((string) $user->email) === ''
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
                    'providerLabel' => $activity->providerLabel(),
                    'locationLabel' => $activity->locationLabel(),
                    'displayTime' => $this->displayTime($activity),
                    'securityUrl' => route('security.index'),
                ]
            );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Login nooit blokkeren vanwege e-mailfout
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Loginbeveiligingsmail kon niet via Brevo worden verzonden.',
                [
                    'user_id' => $user->id,
                    'login_activity_id' => $activity->id,
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }

    private function displayTime(
        LoginActivity $activity
    ): string {
        $timezone = trim(
            (string) $activity->timezone
        );

        if ($timezone === '') {
            $timezone = (string) config(
                'app.timezone',
                'UTC'
            );
        }

        try {
            return $activity
                ->logged_in_at
                ->copy()
                ->timezone($timezone)
                ->format('d-m-Y H:i');
        } catch (Throwable) {
            return $activity
                ->logged_in_at
                ->format('d-m-Y H:i');
        }
    }

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
            ->where('user_id', $userId)
            ->where(
                'logged_in_at',
                '<',
                now()->subDays($retentionDays)
            )
            ->delete();
    }
}
