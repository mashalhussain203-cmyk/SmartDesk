<?php

namespace App\Services;

class DeviceDetectionService
{
    /**
     * Analyseer een browser User-Agent.
     *
     * Geen externe Composer-package nodig.
     *
     * Belangrijk:
     *
     * Browsers geven niet altijd alle hardware-informatie vrij.
     * Bij een iPhone is bijvoorbeeld meestal wel "iPhone" zichtbaar,
     * maar niet betrouwbaar "iPhone 15 Pro".
     *
     * @return array{
     *     device: string,
     *     device_type: string,
     *     browser: string,
     *     operating_system: string,
     *     fingerprint: string
     * }
     */
    public function detect(
        ?string $userAgent
    ): array {
        $userAgent = $this->cleanUserAgent(
            $userAgent
        );

        /*
        |--------------------------------------------------------------------------
        | Geen User-Agent
        |--------------------------------------------------------------------------
        */

        if ($userAgent === '') {
            return [
                'device' => 'Onbekend apparaat',
                'device_type' => 'unknown',
                'browser' => 'Onbekende browser',
                'operating_system' => 'Onbekend besturingssysteem',
                'fingerprint' => hash(
                    'sha256',
                    'unknown-device'
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Detectie
        |--------------------------------------------------------------------------
        */

        $deviceType = $this->detectDeviceType(
            $userAgent
        );

        $device = $this->detectDevice(
            $userAgent,
            $deviceType
        );

        $browser = $this->detectBrowser(
            $userAgent
        );

        $operatingSystem = $this->detectOperatingSystem(
            $userAgent
        );

        /*
        |--------------------------------------------------------------------------
        | Resultaat
        |--------------------------------------------------------------------------
        */

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


    /*
    |--------------------------------------------------------------------------
    | Device type
    |--------------------------------------------------------------------------
    */

    /**
     * Bepaal of het apparaat:
     *
     * - mobile
     * - tablet
     * - desktop
     * - bot
     * - unknown
     */
    private function detectDeviceType(
        string $userAgent
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Bots / crawlers
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/
                    bot|
                    crawler|
                    spider|
                    slurp|
                    bingpreview|
                    facebookexternalhit|
                    googlebot|
                    bingbot|
                    duckduckbot|
                    baiduspider|
                    yandexbot|
                    ahrefsbot|
                    semrushbot
                /ix',
                $userAgent
            )
        ) {
            return 'bot';
        }

        /*
        |--------------------------------------------------------------------------
        | iPad / tablets
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/iPad|Tablet|Nexus\s?(?:7|9|10)|SM-T[A-Z0-9-]*|Tab/i',
                $userAgent
            )
        ) {
            return 'tablet';
        }

        /*
        |--------------------------------------------------------------------------
        | Android-tablet
        |--------------------------------------------------------------------------
        |
        | Veel Android-tablets bevatten "Android", maar geen "Mobile".
        |
        */

        if (
            str_contains(
                $userAgent,
                'Android'
            ) &&
            ! str_contains(
                $userAgent,
                'Mobile'
            )
        ) {
            return 'tablet';
        }

        /*
        |--------------------------------------------------------------------------
        | Telefoon
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/
                    iPhone|
                    iPod|
                    Android.*Mobile|
                    Windows\ Phone|
                    IEMobile|
                    Opera\ Mini|
                    Opera\ Mobi|
                    Mobile
                /ix',
                $userAgent
            )
        ) {
            return 'mobile';
        }

        /*
        |--------------------------------------------------------------------------
        | Desktop
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Windows NT|Macintosh|X11|Linux x86_64|Linux i686|CrOS/i',
                $userAgent
            )
        ) {
            return 'desktop';
        }

        return 'unknown';
    }


    /*
    |--------------------------------------------------------------------------
    | Device
    |--------------------------------------------------------------------------
    */

    /**
     * Bepaal het meest bruikbare apparaatlabel.
     */
    private function detectDevice(
        string $userAgent,
        string $deviceType
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Apple
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'iPhone'
            )
        ) {
            return 'iPhone';
        }

        if (
            str_contains(
                $userAgent,
                'iPad'
            )
        ) {
            return 'iPad';
        }

        if (
            str_contains(
                $userAgent,
                'iPod'
            )
        ) {
            return 'iPod';
        }

        /*
        |--------------------------------------------------------------------------
        | Samsung
        |--------------------------------------------------------------------------
        |
        | Voorbeelden:
        |
        | SM-S928B
        | SM-G991B
        | SM-A556B
        | SM-T870
        |
        */

        if (
            preg_match(
                '/\b(SM-[A-Z0-9-]+)\b/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Samsung '
                . strtoupper(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Google Pixel
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/;\s*(Pixel(?:\s+[A-Za-z0-9 ProXLFold-]+)?)\s+Build\//i',
                $userAgent,
                $matches
            )
        ) {
            return $this->cleanDeviceModel(
                $matches[1]
            ) ?? 'Google Pixel';
        }

        /*
        |--------------------------------------------------------------------------
        | OnePlus
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/;\s*((?:ONEPLUS|OnePlus)\s?[A-Za-z0-9_-]+)\s+Build\//i',
                $userAgent,
                $matches
            )
        ) {
            return $this->cleanDeviceModel(
                $matches[1]
            ) ?? 'OnePlus Android-apparaat';
        }

        /*
        |--------------------------------------------------------------------------
        | Huawei / Honor
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/;\s*((?:HUAWEI|HONOR)[\s-][A-Za-z0-9_-]+)\s+Build\//i',
                $userAgent,
                $matches
            )
        ) {
            return $this->cleanDeviceModel(
                $matches[1]
            ) ?? 'Huawei/Honor Android-apparaat';
        }

        /*
        |--------------------------------------------------------------------------
        | Xiaomi / Redmi / POCO
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/;\s*((?:Redmi|POCO|Mi)\s+[A-Za-z0-9 ProPlusUltra_-]+)\s+Build\//i',
                $userAgent,
                $matches
            )
        ) {
            return $this->cleanDeviceModel(
                $matches[1]
            ) ?? 'Xiaomi Android-apparaat';
        }

        /*
        |--------------------------------------------------------------------------
        | Samsung Browser fallback
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'SamsungBrowser'
            ) &&
            str_contains(
                $userAgent,
                'Android'
            )
        ) {
            return $deviceType === 'tablet'
                ? 'Samsung-tablet'
                : 'Samsung Android-apparaat';
        }

        /*
        |--------------------------------------------------------------------------
        | Overige Android-apparaten
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'Android'
            )
        ) {
            $model = $this->extractAndroidModel(
                $userAgent
            );

            if ($model !== null) {
                return $model;
            }

            return $deviceType === 'tablet'
                ? 'Android-tablet'
                : 'Android-telefoon';
        }

        /*
        |--------------------------------------------------------------------------
        | Windows
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'Windows NT'
            )
        ) {
            return 'Windows-pc';
        }

        /*
        |--------------------------------------------------------------------------
        | macOS
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'Macintosh'
            )
        ) {
            return 'Mac';
        }

        /*
        |--------------------------------------------------------------------------
        | Chromebook
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'CrOS'
            )
        ) {
            return 'Chromebook';
        }

        /*
        |--------------------------------------------------------------------------
        | Linux
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'Linux'
            ) ||
            str_contains(
                $userAgent,
                'X11'
            )
        ) {
            return 'Linux-computer';
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        return match ($deviceType) {
            'mobile' => 'Mobiel apparaat',

            'tablet' => 'Tablet',

            'desktop' => 'Computer',

            'bot' => 'Automatisch systeem',

            default => 'Onbekend apparaat',
        };
    }


    /**
     * Probeer het Android-model uit de User-Agent te halen.
     */
    private function extractAndroidModel(
        string $userAgent
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | Standaard Android UA
        |--------------------------------------------------------------------------
        |
        | Bijvoorbeeld:
        |
        | Android 14; SM-S928B Build/UP1A...
        |
        */

        if (
            preg_match(
                '/Android\s+[0-9.]+;\s*(?:[a-z]{2}(?:-[A-Z]{2})?;\s*)?([^;()]+?)\s+Build\//i',
                $userAgent,
                $matches
            )
        ) {
            return $this->cleanDeviceModel(
                $matches[1]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Alternatieve Android UA
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Android\s+[0-9.]+;\s*([^;)]+)\)/i',
                $userAgent,
                $matches
            )
        ) {
            $model = $this->cleanDeviceModel(
                $matches[1]
            );

            if (
                $model !== null &&
                ! preg_match(
                    '/^[a-z]{2}(?:-[A-Z]{2})?$/',
                    $model
                )
            ) {
                return $model;
            }
        }

        return null;
    }


    /**
     * Maak een gevonden hardwaremodel geschikt voor weergave.
     */
    private function cleanDeviceModel(
        mixed $model
    ): ?string {
        if (! is_scalar($model)) {
            return null;
        }

        $model = trim(
            preg_replace(
                '/\s+/',
                ' ',
                (string) $model
            ) ?? (string) $model
        );

        if ($model === '') {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Onbruikbare generieke waarden
        |--------------------------------------------------------------------------
        */

        $invalid = [
            'wv',
            'mobile',
            'tablet',
            'android',
            'linux',
        ];

        if (
            in_array(
                strtolower($model),
                $invalid,
                true
            )
        ) {
            return null;
        }

        return mb_substr(
            $model,
            0,
            120
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Browser
    |--------------------------------------------------------------------------
    */

    /**
     * Detecteer browser en hoofdversie.
     */
    private function detectBrowser(
        string $userAgent
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Samsung Internet
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/SamsungBrowser\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Samsung Internet '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Microsoft Edge
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/EdgiOS\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Microsoft Edge '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        if (
            preg_match(
                '/EdgA\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Microsoft Edge '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        if (
            preg_match(
                '/Edg\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Microsoft Edge '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Opera
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/OPR\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Opera '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        if (
            preg_match(
                '/Opera\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Opera '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Facebook ingebouwde browser
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/FBAV\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Facebook-browser '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Instagram ingebouwde browser
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Instagram\s+([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Instagram-browser '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | TikTok ingebouwde browser
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/(?:TikTok|musical_ly)[\/\s]([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'TikTok-browser '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Chrome op iOS
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/CriOS\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Chrome '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Firefox op iOS
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/FxiOS\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Firefox '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Android WebView
        |--------------------------------------------------------------------------
        */

        if (
            (
                str_contains(
                    $userAgent,
                    '; wv)'
                ) ||
                str_contains(
                    $userAgent,
                    '; wv;'
                )
            ) &&
            preg_match(
                '/Chrome\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Android WebView '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Chrome
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Chrome\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Chrome '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Firefox
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Firefox\/([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Firefox '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Safari
        |--------------------------------------------------------------------------
        |
        | Safari moet na Chrome / Edge / Opera gecontroleerd worden,
        | omdat veel browsers ook het woord Safari bevatten.
        |
        */

        if (
            preg_match(
                '/Version\/([0-9.]+).*Safari\//i',
                $userAgent,
                $matches
            )
        ) {
            return 'Safari '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Internet Explorer
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/MSIE\s([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Internet Explorer '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        if (
            preg_match(
                '/Trident\/.*rv:([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Internet Explorer '
                . $this->majorVersion(
                    $matches[1]
                );
        }

        return 'Onbekende browser';
    }


    /*
    |--------------------------------------------------------------------------
    | Operating system
    |--------------------------------------------------------------------------
    */

    /**
     * Detecteer het besturingssysteem.
     */
    private function detectOperatingSystem(
        string $userAgent
    ): string {
        /*
        |--------------------------------------------------------------------------
        | iPhone / iPad / iPod
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/(?:iPhone|iPad|iPod).*OS\s([0-9_]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'iOS '
                . str_replace(
                    '_',
                    '.',
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Android
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Android\s([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Android '
                . $this->cleanVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Windows Phone
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Windows Phone(?: OS)?\s([0-9.]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'Windows Phone '
                . $this->cleanVersion(
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Windows
        |--------------------------------------------------------------------------
        |
        | Moderne browsers melden voor Windows 10 én 11 meestal NT 10.0.
        | Daarom kunnen we zonder extra browserinformatie niet betrouwbaar
        | tussen Windows 10 en Windows 11 kiezen.
        |
        */

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

                '6.0' => 'Windows Vista',

                '5.1' => 'Windows XP',

                default => 'Windows',
            };
        }

        /*
        |--------------------------------------------------------------------------
        | macOS
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/Mac OS X\s([0-9_]+)/i',
                $userAgent,
                $matches
            )
        ) {
            return 'macOS '
                . str_replace(
                    '_',
                    '.',
                    $matches[1]
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ChromeOS
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'CrOS'
            )
        ) {
            if (
                preg_match(
                    '/CrOS\s+[^\s]+\s+([0-9.]+)/i',
                    $userAgent,
                    $matches
                )
            ) {
                return 'ChromeOS '
                    . $this->majorVersion(
                        $matches[1]
                    );
            }

            return 'ChromeOS';
        }

        /*
        |--------------------------------------------------------------------------
        | Linux
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $userAgent,
                'Linux'
            ) ||
            str_contains(
                $userAgent,
                'X11'
            )
        ) {
            return 'Linux';
        }

        return 'Onbekend besturingssysteem';
    }


    /*
    |--------------------------------------------------------------------------
    | Fingerprint
    |--------------------------------------------------------------------------
    */

    /**
     * Maak een best-effort apparaatfingerprint.
     *
     * Deze fingerprint wordt uitsluitend gebruikt om binnen hetzelfde
     * account te bepalen of een vergelijkbaar apparaat eerder is gezien.
     *
     * Hij is NIET geschikt voor:
     *
     * - authenticatie
     * - toegangscontrole
     * - unieke fysieke apparaatidentificatie
     */
    private function makeFingerprint(
        string $userAgent,
        string $device,
        string $deviceType,
        string $browser,
        string $operatingSystem
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Browserfamilie
        |--------------------------------------------------------------------------
        |
        | Browserupdates mogen niet iedere keer een "nieuw apparaat"
        | veroorzaken.
        |
        | Chrome 140 -> chrome
        | Safari 18  -> safari
        |
        */

        $browserFamily = $this->stripVersion(
            $browser
        );

        /*
        |--------------------------------------------------------------------------
        | OS-familie
        |--------------------------------------------------------------------------
        |
        | Kleine OS-updates mogen niet onnodig een nieuw apparaat creëren.
        |
        */

        $osFamily = $this->stripVersion(
            $operatingSystem
        );

        /*
        |--------------------------------------------------------------------------
        | User-Agent normaliseren
        |--------------------------------------------------------------------------
        */

        $normalizedAgent = strtolower(
            trim(
                $userAgent
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Versienummers vervangen
        |--------------------------------------------------------------------------
        */

        $normalizedAgent = preg_replace(
            '/\b\d+(?:[._-]\d+)+\b/',
            '#',
            $normalizedAgent
        ) ?? $normalizedAgent;

        /*
        |--------------------------------------------------------------------------
        | Browser-buildnummers normaliseren
        |--------------------------------------------------------------------------
        */

        $normalizedAgent = preg_replace(
            '/\b(chrome|crios|firefox|fxios|version|samsungbrowser|edg|edga|edgios|opr)\/[0-9.#_-]+/i',
            '$1/#',
            $normalizedAgent
        ) ?? $normalizedAgent;

        /*
        |--------------------------------------------------------------------------
        | Android Build-id normaliseren
        |--------------------------------------------------------------------------
        */

        $normalizedAgent = preg_replace(
            '/\bbuild\/[a-z0-9._-]+/i',
            'build/#',
            $normalizedAgent
        ) ?? $normalizedAgent;

        /*
        |--------------------------------------------------------------------------
        | Meervoudige spaties verwijderen
        |--------------------------------------------------------------------------
        */

        $normalizedAgent = preg_replace(
            '/\s+/',
            ' ',
            $normalizedAgent
        ) ?? $normalizedAgent;

        /*
        |--------------------------------------------------------------------------
        | Fingerprintbron
        |--------------------------------------------------------------------------
        */

        $fingerprintSource = implode(
            '|',
            [
                strtolower(
                    trim(
                        $device
                    )
                ),

                strtolower(
                    trim(
                        $deviceType
                    )
                ),

                strtolower(
                    trim(
                        $browserFamily
                    )
                ),

                strtolower(
                    trim(
                        $osFamily
                    )
                ),

                trim(
                    $normalizedAgent
                ),
            ]
        );

        return hash(
            'sha256',
            $fingerprintSource
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * User-Agent opschonen.
     */
    private function cleanUserAgent(
        ?string $userAgent
    ): string {
        $userAgent = trim(
            (string) $userAgent
        );

        if ($userAgent === '') {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | Control characters verwijderen
        |--------------------------------------------------------------------------
        */

        $userAgent = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $userAgent
        ) ?? $userAgent;

        /*
        |--------------------------------------------------------------------------
        | Maximale lengte
        |--------------------------------------------------------------------------
        */

        return mb_substr(
            $userAgent,
            0,
            4000
        );
    }


    /**
     * Alleen hoofdversie teruggeven.
     *
     * 140.0.7339.99 -> 140
     */
    private function majorVersion(
        string $version
    ): string {
        $version = trim(
            $version
        );

        if ($version === '') {
            return '';
        }

        $parts = explode(
            '.',
            $version
        );

        return trim(
            (string) (
                $parts[0]
                ?? $version
            )
        );
    }


    /**
     * Versietekst opschonen.
     */
    private function cleanVersion(
        string $version
    ): string {
        $version = trim(
            $version
        );

        $version = preg_replace(
            '/[^0-9.]/',
            '',
            $version
        ) ?? '';

        return $version !== ''
            ? $version
            : 'onbekend';
    }


    /**
     * Verwijder versienummers uit een leesbaar browser-/OS-label.
     *
     * Chrome 140       -> Chrome
     * Safari 18        -> Safari
     * Android 15       -> Android
     * Windows 10/11    -> Windows
     */
    private function stripVersion(
        string $value
    ): string {
        $value = trim(
            $value
        );

        if ($value === '') {
            return 'unknown';
        }

        $value = preg_replace(
            '/\s+\d+(?:[.\/]\d+)*(?:\/\d+)?$/',
            '',
            $value
        ) ?? $value;

        return trim(
            $value
        );
    }
}