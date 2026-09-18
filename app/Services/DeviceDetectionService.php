<?php

namespace App\Services;

class DeviceDetectionService
{
    /**
     * Analyseer een browser User-Agent zonder externe package.
     *
     * Dit is bewust "best effort":
     * browsers geven bijvoorbeeld niet altijd het exacte iPhone-model vrij.
     *
     * @return array{
     *     device:string,
     *     device_type:string,
     *     browser:string,
     *     operating_system:string,
     *     fingerprint:string
     * }
     */
    public function detect(?string $userAgent): array
    {
        $userAgent = trim((string) $userAgent);

        if ($userAgent === '') {
            return [
                'device' => 'Onbekend apparaat',
                'device_type' => 'unknown',
                'browser' => 'Onbekende browser',
                'operating_system' => 'Onbekend besturingssysteem',
                'fingerprint' => hash('sha256', 'unknown-device'),
            ];
        }

        $deviceType = $this->detectDeviceType($userAgent);
        $device = $this->detectDevice($userAgent, $deviceType);
        $browser = $this->detectBrowser($userAgent);
        $operatingSystem = $this->detectOperatingSystem($userAgent);

        return [
            'device' => $device,
            'device_type' => $deviceType,
            'browser' => $browser,
            'operating_system' => $operatingSystem,
            'fingerprint' => $this->makeFingerprint(
                $userAgent,
                $device,
                $deviceType,
                $browser,
                $operatingSystem
            ),
        ];
    }

    private function detectDeviceType(string $userAgent): string
    {
        if (
            preg_match(
                '/bot|crawler|spider|slurp|bingpreview|facebookexternalhit/i',
                $userAgent
            )
        ) {
            return 'bot';
        }

        if (
            preg_match('/iPad|Tablet|Nexus 7|Nexus 9|SM-T|Tab/i', $userAgent) ||
            (
                str_contains($userAgent, 'Android') &&
                ! str_contains($userAgent, 'Mobile')
            )
        ) {
            return 'tablet';
        }

        if (
            preg_match(
                '/iPhone|iPod|Android.*Mobile|Windows Phone|Mobile/i',
                $userAgent
            )
        ) {
            return 'mobile';
        }

        if (
            preg_match(
                '/Windows NT|Macintosh|X11|Linux x86_64|CrOS/i',
                $userAgent
            )
        ) {
            return 'desktop';
        }

        return 'unknown';
    }

    private function detectDevice(
        string $userAgent,
        string $deviceType
    ): string {
        if (str_contains($userAgent, 'iPhone')) {
            return 'iPhone';
        }

        if (str_contains($userAgent, 'iPad')) {
            return 'iPad';
        }

        if (str_contains($userAgent, 'iPod')) {
            return 'iPod';
        }

        if (
            preg_match(
                '/\b(SM-[A-Z0-9-]+)\b/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Samsung ' . strtoupper($matches[1]);
        }

        if (
            str_contains($userAgent, 'SamsungBrowser') &&
            str_contains($userAgent, 'Android')
        ) {
            return 'Samsung Android-apparaat';
        }

        if (str_contains($userAgent, 'Android')) {
            $model = $this->extractAndroidModel($userAgent);

            if ($model !== null) {
                return $model;
            }

            return $deviceType === 'tablet'
                ? 'Android-tablet'
                : 'Android-telefoon';
        }

        if (str_contains($userAgent, 'Windows NT')) {
            return 'Windows-pc';
        }

        if (str_contains($userAgent, 'Macintosh')) {
            return 'Mac';
        }

        if (str_contains($userAgent, 'CrOS')) {
            return 'Chromebook';
        }

        if (
            str_contains($userAgent, 'Linux') ||
            str_contains($userAgent, 'X11')
        ) {
            return 'Linux-computer';
        }

        return match ($deviceType) {
            'mobile' => 'Mobiel apparaat',
            'tablet' => 'Tablet',
            'desktop' => 'Computer',
            'bot' => 'Automatisch systeem',
            default => 'Onbekend apparaat',
        };
    }

    private function extractAndroidModel(string $userAgent): ?string
    {
        if (
            preg_match(
                '/Android\s+[0-9.]+;\s*(?:[a-z]{2}(?:-[A-Z]{2})?;\s*)?([^;()]+?)\s+Build\//i',
                $userAgent,
                $matches
            )
        ) {
            $model = trim($matches[1]);

            if ($model !== '') {
                return $model;
            }
        }

        return null;
    }

    private function detectBrowser(string $userAgent): string
    {
        if (
            preg_match(
                '/SamsungBrowser\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Samsung Internet ' . $this->majorVersion($matches[1]);
        }

        if (
            preg_match(
                '/EdgA?\/([0-9.]+)|EdgiOS\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Microsoft Edge ' . $this->firstFilledVersion($matches);
        }

        if (
            preg_match(
                '/OPR\/([0-9.]+)|Opera\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Opera ' . $this->firstFilledVersion($matches);
        }

        if (
            preg_match(
                '/CriOS\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Chrome ' . $this->majorVersion($matches[1]);
        }

        if (
            preg_match(
                '/Chrome\/([0-9.]+)/i',
                $userAgent,
                $matches
            ) &&
            ! str_contains($userAgent, 'Chromium')
        ) {
            return 'Chrome ' . $this->majorVersion($matches[1]);
        }

        if (
            preg_match(
                '/FxiOS\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Firefox ' . $this->majorVersion($matches[1]);
        }

        if (
            preg_match(
                '/Firefox\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Firefox ' . $this->majorVersion($matches[1]);
        }

        if (
            preg_match(
                '/Version\/([0-9.]+).*Safari\//i',
                $userAgent,
                $matches
            )
        ) {
            return 'Safari ' . $this->majorVersion($matches[1]);
        }

        return 'Onbekende browser';
    }

    private function detectOperatingSystem(string $userAgent): string
    {
        if (
            preg_match(
                '/(?:iPhone|iPad|iPod).*OS\s([0-9_]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'iOS ' . str_replace('_', '.', $matches[1]);
        }

        if (
            preg_match(
                '/Android\s([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Android ' . $matches[1];
        }

        if (
            preg_match(
                '/Windows NT\s([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return match ($matches[1]) {
                '10.0' => 'Windows 10/11',
                '6.3' => 'Windows 8.1',
                '6.2' => 'Windows 8',
                '6.1' => 'Windows 7',
                default => 'Windows',
            };
        }

        if (
            preg_match(
                '/Mac OS X\s([0-9_]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'macOS ' . str_replace('_', '.', $matches[1]);
        }

        if (str_contains($userAgent, 'CrOS')) {
            return 'ChromeOS';
        }

        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Onbekend besturingssysteem';
    }

    private function makeFingerprint(
        string $userAgent,
        string $device,
        string $deviceType,
        string $browser,
        string $operatingSystem
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Stabielere fingerprint
        |--------------------------------------------------------------------------
        |
        | Browser- en OS-updates veranderen versiegetallen regelmatig.
        | Daarom normaliseren we versies zodat een gewone browserupdate niet
        | meteen als een volledig nieuw apparaat wordt gezien.
        |
        | De fingerprint wordt alleen gebruikt voor "nieuw apparaat"-detectie.
        | Niet voor authenticatie, toegangscontrole of tracking buiten het
        | eigen account.
        |
        */

        $normalizedAgent = strtolower($userAgent);

        $normalizedAgent = preg_replace(
            '/\b\d+(?:[._-]\d+)+\b/',
            '#',
            $normalizedAgent
        ) ?? $normalizedAgent;

        $fingerprintSource = implode('|', [
            trim(strtolower($device)),
            trim(strtolower($deviceType)),
            preg_replace('/\s+\d+(?:\.\d+)*/', '', strtolower($browser)),
            preg_replace('/\s+\d+(?:[.\/]\d+)*/', '', strtolower($operatingSystem)),
            trim($normalizedAgent),
        ]);

        return hash(
            'sha256',
            $fingerprintSource
        );
    }

    private function majorVersion(string $version): string
    {
        $parts = explode('.', $version);

        return $parts[0] ?? $version;
    }

    /**
     * @param array<int, string> $matches
     */
    private function firstFilledVersion(array $matches): string
    {
        foreach (array_slice($matches, 1) as $version) {
            if (trim((string) $version) !== '') {
                return $this->majorVersion((string) $version);
            }
        }

        return '';
    }
}
