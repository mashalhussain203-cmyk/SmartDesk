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
     * Bepaal het IP-adres dat we uitsluitend voor de loginmelding tonen.
     *
     * BELANGRIJK:
     * deze waarde wordt nooit gebruikt voor autorisatie of toegangsbeslissingen.
     */
    public function resolveClientIp(Request $request): string
    {
        $requestIp = trim(
            (string) $request->ip()
        );

        if ($this->isValidPublicIp($requestIp)) {
            return $requestIp;
        }

        /*
        |--------------------------------------------------------------------------
        | Cloud-proxy fallback
        |--------------------------------------------------------------------------
        |
        | Op Railway en andere reverse-proxy omgevingen kan request()->ip()
        | soms het interne proxy-IP opleveren. In dat geval proberen we de
        | gebruikelijke forwarding headers.
        |
        | Omdat headers in sommige configuraties spoofbaar kunnen zijn, wordt
        | dit IP ALLEEN gebruikt voor informatieve loginmeldingen.
        |
        */

        $headerCandidates = [
            $request->header('CF-Connecting-IP'),
            $request->header('X-Real-IP'),
            $this->firstForwardedIp(
                $request->header('X-Forwarded-For')
            ),
            $request->server('REMOTE_ADDR'),
        ];

        foreach ($headerCandidates as $candidate) {
            $candidate = trim(
                (string) $candidate
            );

            if ($this->isValidPublicIp($candidate)) {
                return $candidate;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lokale/private fallback
        |--------------------------------------------------------------------------
        */

        foreach (
            array_merge(
                [$requestIp],
                $headerCandidates
            ) as $candidate
        ) {
            $candidate = trim(
                (string) $candidate
            );

            if ($this->isValidIp($candidate)) {
                return $candidate;
            }
        }

        return 'unknown';
    }

    /**
     * Zoek een geschatte locatie bij een IP-adres.
     *
     * @return array{
     *     city:?string,
     *     region:?string,
     *     country:?string,
     *     country_code:?string,
     *     timezone:?string,
     *     source:string
     * }
     */
    public function lookup(string $ipAddress): array
    {
        $ipAddress = trim($ipAddress);

        if (
            $ipAddress === '' ||
            $ipAddress === 'unknown' ||
            ! $this->isValidIp($ipAddress)
        ) {
            return $this->emptyLocation('unavailable');
        }

        if (! $this->isValidPublicIp($ipAddress)) {
            return $this->emptyLocation('local');
        }

        if (! (bool) config('login-security.geolocation.enabled', true)) {
            return $this->emptyLocation('disabled');
        }

        $cacheHours = max(
            1,
            (int) config(
                'login-security.geolocation.cache_hours',
                24
            )
        );

        $cacheKey = 'login-security:geo:' . hash(
            'sha256',
            $ipAddress
        );

        return Cache::remember(
            $cacheKey,
            now()->addHours($cacheHours),
            fn (): array => $this->requestLocation($ipAddress)
        );
    }

    /**
     * @return array{
     *     city:?string,
     *     region:?string,
     *     country:?string,
     *     country_code:?string,
     *     timezone:?string,
     *     source:string
     * }
     */
    private function requestLocation(string $ipAddress): array
    {
        $urlTemplate = trim(
            (string) config(
                'login-security.geolocation.url',
                'https://ipapi.co/{ip}/json/'
            )
        );

        if ($urlTemplate === '') {
            return $this->emptyLocation('unavailable');
        }

        $url = str_replace(
            '{ip}',
            rawurlencode($ipAddress),
            $urlTemplate
        );

        $timeout = max(
            1,
            (int) config(
                'login-security.geolocation.timeout_seconds',
                4
            )
        );

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'User-Agent' => 'MashalAutomotive-LoginSecurity/1.0',
                ])
                ->connectTimeout(
                    min(3, $timeout)
                )
                ->timeout($timeout)
                ->get($url);

            if (! $response->successful()) {
                Log::warning(
                    'Login IP-locatie kon niet worden opgehaald.',
                    [
                        'status' => $response->status(),
                    ]
                );

                return $this->emptyLocation('unavailable');
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                return $this->emptyLocation('unavailable');
            }

            /*
            |--------------------------------------------------------------------------
            | ipapi.co response
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists('error', $payload) &&
                filter_var(
                    $payload['error'],
                    FILTER_VALIDATE_BOOLEAN
                )
            ) {
                return $this->emptyLocation('unavailable');
            }

            return [
                'city' => $this->cleanNullable(
                    $payload['city'] ?? null
                ),
                'region' => $this->cleanNullable(
                    $payload['region'] ?? null
                ),
                'country' => $this->cleanNullable(
                    $payload['country_name']
                        ?? $payload['country']
                        ?? null
                ),
                'country_code' => $this->cleanNullable(
                    $payload['country_code']
                        ?? $payload['country_code_iso3']
                        ?? null
                ),
                'timezone' => $this->cleanTimezone(
                    $payload['timezone'] ?? null
                ),
                'source' => 'ipapi',
            ];
        } catch (Throwable $exception) {
            Log::warning(
                'Login IP-locatieservice gaf een fout.',
                [
                    'message' => $exception->getMessage(),
                ]
            );

            return $this->emptyLocation('unavailable');
        }
    }

    private function firstForwardedIp(?string $header): ?string
    {
        $header = trim((string) $header);

        if ($header === '') {
            return null;
        }

        $parts = explode(',', $header);

        return trim(
            (string) ($parts[0] ?? '')
        );
    }

    private function isValidIp(string $ipAddress): bool
    {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP
        ) !== false;
    }

    private function isValidPublicIp(string $ipAddress): bool
    {
        if (! $this->isValidIp($ipAddress)) {
            return false;
        }

        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * @return array{
     *     city:?string,
     *     region:?string,
     *     country:?string,
     *     country_code:?string,
     *     timezone:?string,
     *     source:string
     * }
     */
    private function emptyLocation(string $source): array
    {
        return [
            'city' => null,
            'region' => null,
            'country' => null,
            'country_code' => null,
            'timezone' => null,
            'source' => $source,
        ];
    }

    private function cleanNullable(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? mb_substr($value, 0, 120)
            : null;
    }

    private function cleanTimezone(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value['id']
                ?? $value['name']
                ?? null;
        }

        return $this->cleanNullable($value);
    }
}
