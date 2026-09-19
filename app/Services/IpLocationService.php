<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class IpLocationService
{
    /**
     * Bepaal het publieke IP-adres van de bezoeker.
     *
     * Volgorde:
     *
     * 1. Laravel request()->ip()
     *    - met trustProxies() hoort dit op Railway het client-IP te zijn
     *
     * 2. Railway / reverse-proxy headers
     *    - X-Real-IP
     *    - CF-Connecting-IP
     *    - X-Forwarded-For
     *
     * 3. REMOTE_ADDR
     *
     * Dit IP-adres wordt uitsluitend gebruikt voor:
     *
     * - loginhistorie
     * - beveiligingsmail
     * - geschatte IP-geolocatie
     *
     * Nooit als zelfstandig authenticatie- of autorisatiemiddel.
     */
    public function resolveClientIp(
        Request $request
    ): string {
        /*
        |--------------------------------------------------------------------------
        | IP-opslag / verwerking uitgeschakeld
        |--------------------------------------------------------------------------
        */

        if (
            ! (bool) config(
                'login-security.ip.enabled',
                true
            )
        ) {
            return 'unknown';
        }

        /*
        |--------------------------------------------------------------------------
        | Laravel client-IP
        |--------------------------------------------------------------------------
        |
        | Omdat bootstrap/app.php trustProxies('*') gebruikt, kan Laravel
        | forwarded proxyheaders correct interpreteren.
        |
        */

        $requestIp = $this->normalizeIp(
            $request->ip()
        );

        if (
            $requestIp !== null &&
            $this->isValidPublicIp($requestIp)
        ) {
            return $requestIp;
        }

        /*
        |--------------------------------------------------------------------------
        | Proxyheaders
        |--------------------------------------------------------------------------
        */

        $allowForwardedHeaders = (bool) config(
            'login-security.ip.allow_forwarded_headers',
            true
        );

        $headerCandidates = [];

        if ($allowForwardedHeaders) {
            /*
            |--------------------------------------------------------------------------
            | X-Real-IP
            |--------------------------------------------------------------------------
            |
            | Railway/reverse proxies kunnen hier het oorspronkelijke client-IP
            | plaatsen.
            |
            */

            $headerCandidates[] = $this->normalizeIp(
                $request->header(
                    'X-Real-IP'
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Cloudflare
            |--------------------------------------------------------------------------
            |
            | Relevant wanneer later Cloudflare voor de website wordt gebruikt.
            |
            */

            $headerCandidates[] = $this->normalizeIp(
                $request->header(
                    'CF-Connecting-IP'
                )
            );

            /*
            |--------------------------------------------------------------------------
            | X-Forwarded-For
            |--------------------------------------------------------------------------
            |
            | Dit kan meerdere adressen bevatten:
            |
            | client, proxy1, proxy2
            |
            | We zoeken het eerste geldige publieke IP.
            |
            */

            foreach (
                $this->forwardedIps(
                    $request->header(
                        'X-Forwarded-For'
                    )
                ) as $forwardedIp
            ) {
                $headerCandidates[] = $forwardedIp;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Eerste publieke kandidaat
        |--------------------------------------------------------------------------
        */

        foreach ($headerCandidates as $candidate) {
            if (
                $candidate !== null &&
                $this->isValidPublicIp($candidate)
            ) {
                return $candidate;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | REMOTE_ADDR
        |--------------------------------------------------------------------------
        */

        $remoteAddress = $this->normalizeIp(
            $request->server(
                'REMOTE_ADDR'
            )
        );

        if (
            $remoteAddress !== null &&
            $this->isValidPublicIp($remoteAddress)
        ) {
            return $remoteAddress;
        }

        /*
        |--------------------------------------------------------------------------
        | Private/lokale fallback
        |--------------------------------------------------------------------------
        |
        | Dit is vooral nuttig bij:
        |
        | - localhost
        | - Tinker
        | - lokale ontwikkeling
        |
        | Bijvoorbeeld:
        |
        | 127.0.0.1
        | ::1
        | 10.x.x.x
        |
        */

        $allCandidates = array_merge(
            [
                $requestIp,
                $remoteAddress,
            ],
            $headerCandidates
        );

        foreach ($allCandidates as $candidate) {
            if (
                $candidate !== null &&
                $this->isValidIp($candidate)
            ) {
                return $candidate;
            }
        }

        return 'unknown';
    }

    /**
     * Zoek de geschatte geografische locatie van een IP-adres.
     *
     * Dit is GEEN GPS.
     *
     * Een IP-provider kan doorgaans ongeveer bepalen:
     *
     * - stad
     * - regio
     * - land
     * - landcode
     * - timezone
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
    public function lookup(
        string $ipAddress
    ): array {
        $ipAddress = $this->normalizeIp(
            $ipAddress
        );

        /*
        |--------------------------------------------------------------------------
        | Geen geldig IP
        |--------------------------------------------------------------------------
        */

        if (
            $ipAddress === null ||
            ! $this->isValidIp($ipAddress)
        ) {
            return $this->emptyLocation(
                'unavailable'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lokaal / private IP
        |--------------------------------------------------------------------------
        |
        | Bijvoorbeeld een Tinker-test vanaf Railway of localhost.
        |
        */

        if (
            ! $this->isValidPublicIp(
                $ipAddress
            )
        ) {
            return $this->emptyLocation(
                'local'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IP-geolocatie uitgeschakeld
        |--------------------------------------------------------------------------
        */

        if (
            ! (bool) config(
                'login-security.geolocation.enabled',
                true
            )
        ) {
            return $this->emptyLocation(
                'disabled'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Privacyinstelling
        |--------------------------------------------------------------------------
        */

        if (
            ! (bool) config(
                'login-security.privacy.store_estimated_location',
                true
            )
        ) {
            return $this->emptyLocation(
                'disabled'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        |
        | Hetzelfde IP hoeft niet bij iedere login opnieuw extern te
        | worden opgezocht.
        |
        */

        $cacheHours = max(
            1,
            (int) config(
                'login-security.geolocation.cache_hours',
                24
            )
        );

        $cacheKey =
            'login-security:geo:'
            . hash(
                'sha256',
                $ipAddress
            );

        try {
            return Cache::remember(
                $cacheKey,
                now()->addHours(
                    $cacheHours
                ),
                fn (): array =>
                    $this->requestLocation(
                        $ipAddress
                    )
            );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Cache mag login niet blokkeren
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'Loginbeveiliging: locatiecache gaf een fout.',
                [
                    'message' =>
                        $exception->getMessage(),
                ]
            );

            return $this->requestLocation(
                $ipAddress
            );
        }
    }

    /**
     * Vraag de IP-geolocatieprovider om informatie.
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
    private function requestLocation(
        string $ipAddress
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Endpoint
        |--------------------------------------------------------------------------
        */

        $urlTemplate = trim(
            (string) config(
                'login-security.geolocation.url',
                'https://ipapi.co/{ip}/json/'
            )
        );

        if ($urlTemplate === '') {
            return $this->emptyLocation(
                'unavailable'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IP veilig in URL plaatsen
        |--------------------------------------------------------------------------
        */

        $url = str_replace(
            '{ip}',
            rawurlencode(
                $ipAddress
            ),
            $urlTemplate
        );

        /*
        |--------------------------------------------------------------------------
        | Timeout
        |--------------------------------------------------------------------------
        */

        $timeout = max(
            1,
            min(
                15,
                (int) config(
                    'login-security.geolocation.timeout_seconds',
                    4
                )
            )
        );

        $connectTimeout = max(
            1,
            min(
                3,
                $timeout
            )
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'User-Agent' =>
                        'MashalAutomotive-LoginSecurity/2.0',
                ])
                ->connectTimeout(
                    $connectTimeout
                )
                ->timeout(
                    $timeout
                )
                ->get(
                    $url
                );

            /*
            |--------------------------------------------------------------------------
            | HTTP-fout
            |--------------------------------------------------------------------------
            */

            if (! $response->successful()) {
                Log::warning(
                    'Loginbeveiliging: IP-locatie kon niet worden opgehaald.',
                    [
                        'status' =>
                            $response->status(),

                        /*
                        | Geen API-responsebody loggen:
                        | daar kan onnodige metadata in staan.
                        */
                    ]
                );

                return $this->emptyLocation(
                    'unavailable'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | JSON
            |--------------------------------------------------------------------------
            */

            $payload = $response->json();

            if (! is_array($payload)) {
                return $this->emptyLocation(
                    'unavailable'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Provider meldt fout
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'error',
                    $payload
                ) &&
                filter_var(
                    $payload['error'],
                    FILTER_VALIDATE_BOOLEAN
                )
            ) {
                Log::warning(
                    'Loginbeveiliging: IP-locatieprovider retourneerde een fout.',
                    [
                        'reason' =>
                            $this->cleanNullable(
                                $payload['reason']
                                    ?? null
                            ),
                    ]
                );

                return $this->emptyLocation(
                    'unavailable'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Resultaat normaliseren
            |--------------------------------------------------------------------------
            */

            return [
                'city' =>
                    $this->cleanNullable(
                        $payload['city']
                            ?? null
                    ),

                'region' =>
                    $this->cleanNullable(
                        $payload['region']
                            ?? $payload['region_name']
                            ?? null
                    ),

                'country' =>
                    $this->cleanNullable(
                        $payload['country_name']
                            ?? $payload['country']
                            ?? null
                    ),

                'country_code' =>
                    $this->cleanCountryCode(
                        $payload['country_code']
                            ?? null
                    ),

                'timezone' =>
                    $this->cleanTimezone(
                        $payload['timezone']
                            ?? null
                    ),

                'source' => 'ipapi',
            ];
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Externe API mag login nooit blokkeren
            |--------------------------------------------------------------------------
            */

            Log::warning(
                'Loginbeveiliging: IP-locatieservice gaf een fout.',
                [
                    'message' =>
                        $exception->getMessage(),
                ]
            );

            return $this->emptyLocation(
                'unavailable'
            );
        }
    }

    /**
     * Parse X-Forwarded-For.
     *
     * Voorbeeld:
     *
     * 203.0.113.10, 172.16.0.5, 10.0.0.3
     *
     * @return array<int, string>
     */
    private function forwardedIps(
        mixed $header
    ): array {
        if (! is_scalar($header)) {
            return [];
        }

        $header = trim(
            (string) $header
        );

        if ($header === '') {
            return [];
        }

        $result = [];

        foreach (
            explode(
                ',',
                $header
            ) as $part
        ) {
            $ip = $this->normalizeIp(
                $part
            );

            if (
                $ip !== null &&
                $this->isValidIp($ip)
            ) {
                $result[] = $ip;
            }
        }

        return array_values(
            array_unique(
                $result
            )
        );
    }

    /**
     * Normaliseer een IP-adres.
     *
     * Ondersteunt onder andere:
     *
     * 203.0.113.10
     * "203.0.113.10"
     * [2001:db8::1]
     */
    private function normalizeIp(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Quotes verwijderen
        |--------------------------------------------------------------------------
        */

        $value = trim(
            $value,
            " \t\n\r\0\x0B\"'"
        );

        /*
        |--------------------------------------------------------------------------
        | IPv6 tussen vierkante haken
        |--------------------------------------------------------------------------
        |
        | [2001:db8::1]
        |
        */

        if (
            str_starts_with(
                $value,
                '['
            ) &&
            str_ends_with(
                $value,
                ']'
            )
        ) {
            $value = substr(
                $value,
                1,
                -1
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IPv4 met poort
        |--------------------------------------------------------------------------
        |
        | Bijvoorbeeld:
        |
        | 203.0.113.10:443
        |
        */

        if (
            substr_count(
                $value,
                ':'
            ) === 1
        ) {
            [$possibleIp, $possiblePort] =
                array_pad(
                    explode(
                        ':',
                        $value,
                        2
                    ),
                    2,
                    null
                );

            if (
                $possibleIp !== null &&
                $possiblePort !== null &&
                ctype_digit(
                    $possiblePort
                ) &&
                $this->isValidIp(
                    $possibleIp
                )
            ) {
                $value = $possibleIp;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | IPv6 zone identifier verwijderen
        |--------------------------------------------------------------------------
        |
        | Bijvoorbeeld:
        |
        | fe80::1%eth0
        |
        */

        if (
            str_contains(
                $value,
                '%'
            )
        ) {
            $value = explode(
                '%',
                $value,
                2
            )[0];
        }

        return $this->isValidIp(
            $value
        )
            ? $value
            : null;
    }

    /**
     * Controleer of het een syntactisch geldig IPv4/IPv6-adres is.
     */
    private function isValidIp(
        string $ipAddress
    ): bool {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP
        ) !== false;
    }

    /**
     * Controleer of het een publiek routeerbaar IP-adres is.
     *
     * Private/reserved adressen zoals:
     *
     * 127.0.0.1
     * ::1
     * 10.0.0.0/8
     * 172.16.0.0/12
     * 192.168.0.0/16
     *
     * worden niet naar de externe geolocatieprovider gestuurd.
     */
    private function isValidPublicIp(
        string $ipAddress
    ): bool {
        if (
            ! $this->isValidIp(
                $ipAddress
            )
        ) {
            return false;
        }

        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * Lege locatie-response.
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
    private function emptyLocation(
        string $source
    ): array {
        return [
            'city' => null,

            'region' => null,

            'country' => null,

            'country_code' => null,

            'timezone' => null,

            'source' => $source,
        ];
    }

    /**
     * Maak een veilige nullable string.
     */
    private function cleanNullable(
        mixed $value
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return null;
        }

        return mb_substr(
            $value,
            0,
            120
        );
    }

    /**
     * Landcode normaliseren.
     *
     * Bijvoorbeeld:
     *
     * nl -> NL
     * us -> US
     */
    private function cleanCountryCode(
        mixed $value
    ): ?string {
        $value = $this->cleanNullable(
            $value
        );

        if ($value === null) {
            return null;
        }

        $value = strtoupper(
            $value
        );

        return mb_substr(
            $value,
            0,
            3
        );
    }

    /**
     * Timezone controleren.
     *
     * Bijvoorbeeld:
     *
     * Europe/Amsterdam
     * America/New_York
     */
    private function cleanTimezone(
        mixed $value
    ): ?string {
        if (is_array($value)) {
            $value =
                $value['id']
                ?? $value['name']
                ?? null;
        }

        $timezone = $this->cleanNullable(
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