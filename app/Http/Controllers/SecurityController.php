<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SecurityController extends Controller
{
    /**
     * Toon de beveiligingspagina met recente loginactiviteiten
     * van uitsluitend de ingelogde gebruiker.
     */
    public function index(
        Request $request
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Ingelogde gebruiker
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        abort_unless(
            $user !== null,
            401
        );

        /*
        |--------------------------------------------------------------------------
        | Maximum aantal loginactiviteiten
        |--------------------------------------------------------------------------
        */

        $limit = max(
            10,
            min(
                100,
                (int) config(
                    'login-security.history_limit',
                    50
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Loginactiviteiten ophalen
        |--------------------------------------------------------------------------
        */

        $activities = LoginActivity::query()
            ->where(
                'user_id',
                $user->id
            )
            ->latest(
                'logged_in_at'
            )
            ->latest(
                'id'
            )
            ->limit(
                $limit
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Totaal aantal opgeslagen activiteiten
        |--------------------------------------------------------------------------
        */

        $totalActivityCount = LoginActivity::query()
            ->where(
                'user_id',
                $user->id
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Nieuwe apparaten
        |--------------------------------------------------------------------------
        */

        $newDeviceCount = $activities
            ->where(
                'is_new_device',
                true
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Bekende apparaten
        |--------------------------------------------------------------------------
        */

        $knownDeviceCount = $activities
            ->where(
                'is_new_device',
                false
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Laatste login
        |--------------------------------------------------------------------------
        */

        $latestActivity = $activities
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Precieze locatie-statistieken
        |--------------------------------------------------------------------------
        */

        $preciseLocationCount = 0;

        if (
            $this->loginActivityColumnExists(
                'latitude'
            ) &&
            $this->loginActivityColumnExists(
                'longitude'
            )
        ) {
            $preciseLocationCount = $activities
                ->filter(
                    static fn (
                        LoginActivity $activity
                    ): bool =>
                        $activity->latitude !== null &&
                        $activity->longitude !== null
                )
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | Locatietoestemming
        |--------------------------------------------------------------------------
        */

        $locationPermissionGrantedCount = 0;

        if (
            $this->loginActivityColumnExists(
                'location_permission'
            )
        ) {
            $locationPermissionGrantedCount = $activities
                ->filter(
                    static fn (
                        LoginActivity $activity
                    ): bool =>
                        strtolower(
                            trim(
                                (string) $activity->location_permission
                            )
                        ) === 'granted'
                )
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | Beveiligingsmail statistieken
        |--------------------------------------------------------------------------
        */

        $notificationSentCount = 0;
        $notificationFailedCount = 0;

        if (
            $this->loginActivityColumnExists(
                'notification_sent_at'
            )
        ) {
            $notificationSentCount = $activities
                ->filter(
                    static fn (
                        LoginActivity $activity
                    ): bool =>
                        $activity->notification_sent_at !== null
                )
                ->count();
        }

        if (
            $this->loginActivityColumnExists(
                'notification_failed_at'
            )
        ) {
            $notificationFailedCount = $activities
                ->filter(
                    static fn (
                        LoginActivity $activity
                    ): bool =>
                        $activity->notification_failed_at !== null
                )
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'site.security',
            [
                'activities' =>
                    $activities,

                'latestActivity' =>
                    $latestActivity,

                'totalActivityCount' =>
                    $totalActivityCount,

                'newDeviceCount' =>
                    $newDeviceCount,

                'knownDeviceCount' =>
                    $knownDeviceCount,

                'preciseLocationCount' =>
                    $preciseLocationCount,

                'locationPermissionGrantedCount' =>
                    $locationPermissionGrantedCount,

                'notificationSentCount' =>
                    $notificationSentCount,

                'notificationFailedCount' =>
                    $notificationFailedCount,

                'historyLimit' =>
                    $limit,
            ]
        );
    }

    /**
     * Sla browser-securitycontext tijdelijk op in de Laravel-sessie.
     *
     * Deze endpoint wordt gebruikt vóór de daadwerkelijke login.
     *
     * Daardoor zijn gegevens ook beschikbaar nadat de gebruiker
     * bijvoorbeeld via Google, Facebook, GitHub of TikTok terugkomt.
     *
     * Mogelijke gegevens:
     *
     * - browser timezone
     * - latitude
     * - longitude
     * - GPS accuracy
     * - location permission
     *
     * GPS-coördinaten worden uitsluitend opgeslagen wanneer
     * location_permission = granted.
     */
    public function storeBrowserContext(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Login-security uitgeschakeld
        |--------------------------------------------------------------------------
        */

        if (
            ! (bool) config(
                'login-security.enabled',
                true
            )
        ) {
            return response()->json([
                'success' => true,
                'stored' => false,
                'reason' => 'disabled',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validatie
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'browser_timezone' => [
                'nullable',
                'string',
                'max:100',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'location_accuracy' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100000',
            ],

            'location_permission' => [
                'nullable',
                'string',
                Rule::in([
                    'granted',
                    'denied',
                    'prompt',
                    'unavailable',
                    'unsupported',
                    'unknown',
                ]),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Browser timezone
        |--------------------------------------------------------------------------
        */

        $browserTimezone = $this->normalizeTimezone(
            $validated['browser_timezone']
                ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | Locatietoestemming
        |--------------------------------------------------------------------------
        */

        $locationPermission = strtolower(
            trim(
                (string) (
                    $validated['location_permission']
                    ?? 'unknown'
                )
            )
        );

        if (
            ! in_array(
                $locationPermission,
                [
                    'granted',
                    'denied',
                    'prompt',
                    'unavailable',
                    'unsupported',
                    'unknown',
                ],
                true
            )
        ) {
            $locationPermission = 'unknown';
        }

        /*
        |--------------------------------------------------------------------------
        | Precieze locatie instellingen
        |--------------------------------------------------------------------------
        */

        $preciseLocationEnabled = (bool) config(
            'login-security.precise_location.enabled',
            true
        );

        $storePreciseLocation = (bool) config(
            'login-security.privacy.store_precise_location_with_consent',
            true
        );

        /*
        |--------------------------------------------------------------------------
        | GPS-data
        |--------------------------------------------------------------------------
        */

        $latitude = $this->nullableFloat(
            $validated['latitude']
                ?? null
        );

        $longitude = $this->nullableFloat(
            $validated['longitude']
                ?? null
        );

        $locationAccuracy = $this->nullableFloat(
            $validated['location_accuracy']
                ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | Geen toestemming = geen GPS opslaan
        |--------------------------------------------------------------------------
        */

        if (
            ! $preciseLocationEnabled ||
            ! $storePreciseLocation ||
            $locationPermission !== 'granted'
        ) {
            $latitude = null;
            $longitude = null;
            $locationAccuracy = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Coördinaten moeten compleet zijn
        |--------------------------------------------------------------------------
        */

        if (
            $latitude === null ||
            $longitude === null
        ) {
            $latitude = null;
            $longitude = null;
            $locationAccuracy = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Sessie controleren
        |--------------------------------------------------------------------------
        */

        if (! $request->hasSession()) {
            return response()->json(
                [
                    'success' => false,
                    'stored' => false,
                    'message' =>
                        'Browsercontext kon niet in de sessie worden opgeslagen.',
                ],
                500
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Browser timezone opslaan
        |--------------------------------------------------------------------------
        */

        if (
            (bool) config(
                'login-security.timezone.capture_browser_timezone',
                true
            )
        ) {
            if ($browserTimezone !== null) {
                $request->session()->put(
                    'login_security.browser_timezone',
                    $browserTimezone
                );
            } else {
                $request->session()->forget(
                    'login_security.browser_timezone'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Locatietoestemming altijd onthouden
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'login_security.location_permission',
            $locationPermission
        );

        /*
        |--------------------------------------------------------------------------
        | GPS opslaan
        |--------------------------------------------------------------------------
        */

        if (
            $latitude !== null &&
            $longitude !== null
        ) {
            $request->session()->put(
                'login_security.latitude',
                $latitude
            );

            $request->session()->put(
                'login_security.longitude',
                $longitude
            );

            if ($locationAccuracy !== null) {
                $request->session()->put(
                    'login_security.location_accuracy',
                    $locationAccuracy
                );
            } else {
                $request->session()->forget(
                    'login_security.location_accuracy'
                );
            }
        } else {
            /*
            |--------------------------------------------------------------------------
            | Oude GPS-data verwijderen
            |--------------------------------------------------------------------------
            |
            | Bijvoorbeeld wanneer iemand bij een volgende login
            | locatie weigert.
            |
            */

            $request->session()->forget([
                'login_security.latitude',
                'login_security.longitude',
                'login_security.location_accuracy',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Tijdstip context opslaan
        |--------------------------------------------------------------------------
        |
        | Alleen informatief.
        |
        */

        $request->session()->put(
            'login_security.context_captured_at',
            now()->toIso8601String()
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        |
        | We sturen de coördinaten niet terug naar de browserresponse.
        |
        */

        return response()->json([
            'success' => true,

            'stored' => true,

            'browser_timezone' =>
                $browserTimezone,

            'location_permission' =>
                $locationPermission,

            'precise_location_stored' =>
                $latitude !== null &&
                $longitude !== null,
        ]);
    }

    /**
     * Verwijder alle loginhistorie van uitsluitend
     * de huidige ingelogde gebruiker.
     *
     * Dit verwijdert NIET:
     *
     * - account
     * - wachtwoord
     * - huidige sessie
     * - Google-koppeling
     * - Facebook-koppeling
     * - GitHub-koppeling
     * - TikTok-koppeling
     */
    public function destroyHistory(
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Ingelogde gebruiker
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        abort_unless(
            $user !== null,
            401
        );

        /*
        |--------------------------------------------------------------------------
        | Eigen loginhistorie verwijderen
        |--------------------------------------------------------------------------
        */

        $deletedCount = LoginActivity::query()
            ->where(
                'user_id',
                $user->id
            )
            ->delete();

        /*
        |--------------------------------------------------------------------------
        | Tijdelijke browsermetadata opruimen
        |--------------------------------------------------------------------------
        */

        $this->forgetBrowserContext(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Bericht
        |--------------------------------------------------------------------------
        */

        $message = match (true) {
            $deletedCount === 0 =>
                'Er was geen loginhistorie om te verwijderen.',

            $deletedCount === 1 =>
                'Je loginactiviteit is verwijderd.',

            default =>
                'Je loginhistorie is verwijderd.',
        };

        return redirect()
            ->route(
                'security.index'
            )
            ->with(
                'success',
                $message
            );
    }

    /**
     * Verwijder tijdelijke browsersecuritycontext.
     */
    private function forgetBrowserContext(
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
     * Controleer veilig of een kolom bestaat.
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
     * Controleer en normaliseer een IANA timezone.
     *
     * Bijvoorbeeld:
     *
     * Europe/Amsterdam
     * America/New_York
     * Asia/Karachi
     */
    private function normalizeTimezone(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $timezone = trim(
            (string) $value
        );

        if ($timezone === '') {
            return null;
        }

        if (
            mb_strlen(
                $timezone
            ) > 100
        ) {
            return null;
        }

        try {
            new DateTimeZone(
                $timezone
            );

            return $timezone;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Maak een nullable float.
     */
    private function nullableFloat(
        mixed $value
    ): ?float {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return is_finite(
            $number
        )
            ? $number
            : null;
    }
}