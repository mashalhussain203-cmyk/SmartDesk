<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use DateTimeZone;
use Illuminate\Database\Eloquent\Collection;
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
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user !== null,
            401
        );

        $historyLimit = $this->historyLimit();

        $activities = LoginActivity::query()
            ->where('user_id', $user->id)
            ->latest('logged_in_at')
            ->latest('id')
            ->limit($historyLimit)
            ->get();

        $totalActivityCount = LoginActivity::query()
            ->where('user_id', $user->id)
            ->count();

        $newDeviceCount = $activities
            ->where('is_new_device', true)
            ->count();

        $knownDeviceCount = $activities
            ->where('is_new_device', false)
            ->count();

        $latestActivity = $activities->first();

        $preciseLocationCount =
            $this->countPreciseLocations($activities);

        $locationPermissionGrantedCount =
            $this->countGrantedLocationPermissions($activities);

        $notificationSentCount =
            $this->countActivitiesWithColumnValue(
                $activities,
                'notification_sent_at'
            );

        $notificationFailedCount =
            $this->countActivitiesWithColumnValue(
                $activities,
                'notification_failed_at'
            );

        return view(
            'site.security',
            [
                'activities' => $activities,
                'latestActivity' => $latestActivity,
                'totalActivityCount' => $totalActivityCount,
                'newDeviceCount' => $newDeviceCount,
                'knownDeviceCount' => $knownDeviceCount,
                'preciseLocationCount' => $preciseLocationCount,
                'locationPermissionGrantedCount' =>
                    $locationPermissionGrantedCount,
                'notificationSentCount' => $notificationSentCount,
                'notificationFailedCount' => $notificationFailedCount,
                'historyLimit' => $historyLimit,
            ]
        );
    }

    /**
     * Sla tijdelijke browser-securitycontext op in de huidige sessie.
     *
     * Deze endpoint wordt vóór een login gebruikt zodat de
     * LoginSecurityService na wachtwoord-, e-mailcode-, magic-link-
     * of OAuth-login dezelfde browsercontext kan gebruiken.
     *
     * Precieze GPS-coördinaten worden uitsluitend opgeslagen wanneer:
     * - precise location is ingeschakeld;
     * - opslag met toestemming is toegestaan;
     * - location_permission gelijk is aan "granted";
     * - latitude én longitude geldig zijn.
     */
    public function storeBrowserContext(
        Request $request
    ): JsonResponse {
        if (! $this->loginSecurityEnabled()) {
            return response()->json([
                'success' => true,
                'stored' => false,
                'reason' => 'disabled',
            ]);
        }

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

        $browserTimezone = $this->normalizeTimezone(
            $validated['browser_timezone']
                ?? null
        );

        $locationPermission = $this->normalizeLocationPermission(
            $validated['location_permission']
                ?? null
        );

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

        if (
            ! $this->mayStorePreciseLocation(
                $locationPermission
            )
        ) {
            $latitude = null;
            $longitude = null;
            $locationAccuracy = null;
        }

        if (
            $latitude === null ||
            $longitude === null
        ) {
            $latitude = null;
            $longitude = null;
            $locationAccuracy = null;
        }

        $this->storeTimezone(
            $request,
            $browserTimezone
        );

        $request->session()->put(
            'login_security.location_permission',
            $locationPermission
        );

        $this->storeCoordinates(
            $request,
            $latitude,
            $longitude,
            $locationAccuracy
        );

        $request->session()->put(
            'login_security.context_captured_at',
            now()->toIso8601String()
        );

        return response()->json([
            'success' => true,
            'stored' => true,
            'browser_timezone' => $browserTimezone,
            'location_permission' => $locationPermission,
            'precise_location_stored' =>
                $latitude !== null &&
                $longitude !== null,
        ]);
    }

    /**
     * Verwijder alle loginhistorie van uitsluitend de huidige gebruiker.
     *
     * Dit verwijdert niet:
     * - het account;
     * - wachtwoorden;
     * - huidige sessie;
     * - OAuth-koppelingen;
     * - pending_image.
     */
    public function destroyHistory(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user !== null,
            401
        );

        $deletedCount = LoginActivity::query()
            ->where('user_id', $user->id)
            ->delete();

        $this->forgetBrowserContext(
            $request
        );

        $message = match (true) {
            $deletedCount === 0 =>
                'Er was geen loginhistorie om te verwijderen.',

            $deletedCount === 1 =>
                'Je loginactiviteit is verwijderd.',

            default =>
                'Je loginhistorie is verwijderd.',
        };

        return redirect()
            ->route('security.index')
            ->with(
                'success',
                $message
            );
    }

    /**
     * Maximum aantal activiteiten dat op de beveiligingspagina wordt getoond.
     */
    private function historyLimit(): int
    {
        return max(
            10,
            min(
                100,
                (int) config(
                    'login-security.history_limit',
                    50
                )
            )
        );
    }

    /**
     * Controleer of login-security is ingeschakeld.
     */
    private function loginSecurityEnabled(): bool
    {
        return (bool) config(
            'login-security.enabled',
            true
        );
    }

    /**
     * Tel activiteiten met opgeslagen precieze coördinaten.
     *
     * @param Collection<int, LoginActivity> $activities
     */
    private function countPreciseLocations(
        Collection $activities
    ): int {
        if (
            ! $this->loginActivityColumnExists('latitude') ||
            ! $this->loginActivityColumnExists('longitude')
        ) {
            return 0;
        }

        return $activities
            ->filter(
                static fn (
                    LoginActivity $activity
                ): bool =>
                    $activity->latitude !== null &&
                    $activity->longitude !== null
            )
            ->count();
    }

    /**
     * Tel activiteiten waarvoor locatietoestemming expliciet was verleend.
     *
     * @param Collection<int, LoginActivity> $activities
     */
    private function countGrantedLocationPermissions(
        Collection $activities
    ): int {
        if (
            ! $this->loginActivityColumnExists(
                'location_permission'
            )
        ) {
            return 0;
        }

        return $activities
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

    /**
     * Tel activiteiten waarvoor een optionele datumkolom een waarde heeft.
     *
     * @param Collection<int, LoginActivity> $activities
     */
    private function countActivitiesWithColumnValue(
        Collection $activities,
        string $column
    ): int {
        if (
            ! $this->loginActivityColumnExists(
                $column
            )
        ) {
            return 0;
        }

        return $activities
            ->filter(
                static fn (
                    LoginActivity $activity
                ): bool =>
                    $activity->getAttribute($column) !== null
            )
            ->count();
    }

    /**
     * Sla de browser-timezone op of ruim de oude waarde op.
     */
    private function storeTimezone(
        Request $request,
        ?string $browserTimezone
    ): void {
        if (
            ! (bool) config(
                'login-security.timezone.capture_browser_timezone',
                true
            )
        ) {
            return;
        }

        if ($browserTimezone !== null) {
            $request->session()->put(
                'login_security.browser_timezone',
                $browserTimezone
            );

            return;
        }

        $request->session()->forget(
            'login_security.browser_timezone'
        );
    }

    /**
     * Bepaal of precieze locatie opgeslagen mag worden.
     */
    private function mayStorePreciseLocation(
        string $permission
    ): bool {
        if ($permission !== 'granted') {
            return false;
        }

        if (
            ! (bool) config(
                'login-security.precise_location.enabled',
                true
            )
        ) {
            return false;
        }

        return (bool) config(
            'login-security.privacy.store_precise_location_with_consent',
            true
        );
    }

    /**
     * Sla geldige coördinaten op of verwijder oude GPS-data.
     */
    private function storeCoordinates(
        Request $request,
        ?float $latitude,
        ?float $longitude,
        ?float $locationAccuracy
    ): void {
        if (
            $latitude === null ||
            $longitude === null
        ) {
            $request->session()->forget([
                'login_security.latitude',
                'login_security.longitude',
                'login_security.location_accuracy',
            ]);

            return;
        }

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

            return;
        }

        $request->session()->forget(
            'login_security.location_accuracy'
        );
    }

    /**
     * Verwijder tijdelijke browser-securitycontext.
     *
     * Let op: pending_image wordt bewust niet verwijderd.
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
     * Controleer veilig of een kolom in login_activities bestaat.
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
     * Normaliseer een locatietoestemming.
     */
    private function normalizeLocationPermission(
        mixed $value
    ): string {
        if (! is_scalar($value)) {
            return 'unknown';
        }

        $permission = strtolower(
            trim(
                (string) $value
            )
        );

        return in_array(
            $permission,
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
            ? $permission
            : 'unknown';
    }

    /**
     * Controleer en normaliseer een IANA-timezone.
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

        if (
            $timezone === '' ||
            mb_strlen($timezone) > 100
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
     * Zet een optionele numerieke waarde veilig om naar float.
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

        return is_finite($number)
            ? $number
            : null;
    }
}
