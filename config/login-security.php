<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Login Security
    |--------------------------------------------------------------------------
    |
    | Hoofdschakelaar voor de volledige loginbeveiligingsmodule.
    |
    */

    'enabled' => env(
        'LOGIN_SECURITY_ENABLED',
        true
    ),


    /*
    |--------------------------------------------------------------------------
    | Bewaartermijn
    |--------------------------------------------------------------------------
    |
    | Loginactiviteit kan persoonsgegevens bevatten zoals:
    |
    | - IP-adres
    | - browser
    | - apparaat
    | - geschatte locatie
    | - eventueel GPS-locatie na toestemming
    |
    | Daarom verwijderen we oude loginactiviteiten automatisch.
    |
    */

    'retention_days' => (int) env(
        'LOGIN_SECURITY_RETENTION_DAYS',
        90
    ),


    /*
    |--------------------------------------------------------------------------
    | Maximum loginhistorie
    |--------------------------------------------------------------------------
    |
    | Maximum aantal loginactiviteiten dat op de beveiligingspagina
    | wordt weergegeven.
    |
    */

    'history_limit' => (int) env(
        'LOGIN_SECURITY_HISTORY_LIMIT',
        50
    ),


    /*
    |--------------------------------------------------------------------------
    | Beveiligingsmail
    |--------------------------------------------------------------------------
    |
    | Verstuur via de bestaande BrevoMailService een melding
    | wanneer een succesvolle login wordt geregistreerd.
    |
    */

    'email' => [

        'enabled' => env(
            'LOGIN_SECURITY_SEND_EMAIL',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Iedere login melden
        |--------------------------------------------------------------------------
        |
        | true:
        |   e-mail bij iedere succesvolle login.
        |
        | false:
        |   kan later gebruikt worden voor alleen bijzondere logins.
        |
        */

        'every_login' => env(
            'LOGIN_SECURITY_EMAIL_EVERY_LOGIN',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Nieuw apparaat extra markeren
        |--------------------------------------------------------------------------
        */

        'highlight_new_device' => env(
            'LOGIN_SECURITY_HIGHLIGHT_NEW_DEVICE',
            true
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | IP-adres
    |--------------------------------------------------------------------------
    |
    | Op Railway draait Laravel achter een reverse proxy.
    |
    | bootstrap/app.php moet daarom trustProxies gebruiken.
    |
    | De service probeert daarna het echte publieke client-IP
    | van de browserrequest te bepalen.
    |
    */

    'ip' => [

        'enabled' => env(
            'LOGIN_SECURITY_STORE_IP',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Forwarded headers
        |--------------------------------------------------------------------------
        |
        | Alleen informatief gebruiken.
        |
        | De login-securitymodule gebruikt het IP nooit als enige
        | authenticatie- of autorisatiecontrole.
        |
        */

        'allow_forwarded_headers' => env(
            'LOGIN_SECURITY_ALLOW_FORWARDED_HEADERS',
            true
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | IP-geolocatie
    |--------------------------------------------------------------------------
    |
    | Hiermee wordt een publieke client-IP vertaald naar een
    | GESCHATTE locatie.
    |
    | Mogelijke gegevens:
    |
    | - stad
    | - regio
    | - land
    | - landcode
    | - timezone
    |
    | Dit is geen GPS-locatie.
    |
    */

    'geolocation' => [

        'enabled' => env(
            'LOGIN_SECURITY_GEOLOCATION_ENABLED',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Provider URL
        |--------------------------------------------------------------------------
        |
        | {ip} wordt automatisch vervangen door het publieke IP-adres.
        |
        */

        'url' => env(
            'LOGIN_SECURITY_GEOLOCATION_URL',
            'https://ipapi.co/{ip}/json/'
        ),

        /*
        |--------------------------------------------------------------------------
        | Timeout
        |--------------------------------------------------------------------------
        |
        | Als de externe locatieservice niet reageert, mag de login
        | daardoor nooit vastlopen.
        |
        */

        'timeout_seconds' => (int) env(
            'LOGIN_SECURITY_GEOLOCATION_TIMEOUT',
            4
        ),

        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        |
        | Voorkomt dat hetzelfde IP voortdurend opnieuw wordt opgezocht.
        |
        */

        'cache_hours' => (int) env(
            'LOGIN_SECURITY_GEOLOCATION_CACHE_HOURS',
            24
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Precieze browserlocatie
    |--------------------------------------------------------------------------
    |
    | Browser GPS / Wi-Fi locatie via:
    |
    | navigator.geolocation.getCurrentPosition(...)
    |
    | Deze locatie mag uitsluitend worden opgehaald nadat de gebruiker
    | daar in de browser toestemming voor geeft.
    |
    | Als toestemming wordt geweigerd blijft de login gewoon werken.
    |
    */

    'precise_location' => [

        'enabled' => env(
            'LOGIN_SECURITY_PRECISE_LOCATION_ENABLED',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Altijd toestemming vereisen
        |--------------------------------------------------------------------------
        */

        'require_consent' => true,

        /*
        |--------------------------------------------------------------------------
        | High accuracy
        |--------------------------------------------------------------------------
        |
        | Op mobiele apparaten kan de browser hiermee proberen
        | een nauwkeurigere locatie te verkrijgen.
        |
        */

        'high_accuracy' => env(
            'LOGIN_SECURITY_GPS_HIGH_ACCURACY',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Browser timeout
        |--------------------------------------------------------------------------
        |
        | Milliseconden.
        |
        */

        'timeout_ms' => (int) env(
            'LOGIN_SECURITY_GPS_TIMEOUT_MS',
            10000
        ),

        /*
        |--------------------------------------------------------------------------
        | Maximum age
        |--------------------------------------------------------------------------
        |
        | Hoe oud een eerder door de browser bepaalde locatie maximaal
        | mag zijn.
        |
        | Milliseconden.
        |
        */

        'maximum_age_ms' => (int) env(
            'LOGIN_SECURITY_GPS_MAXIMUM_AGE_MS',
            60000
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Browser timezone
    |--------------------------------------------------------------------------
    |
    | De browser kan bijvoorbeeld doorgeven:
    |
    | Europe/Amsterdam
    | Europe/London
    | America/New_York
    |
    | Dit gebruiken we om een login in de lokale tijd van de
    | gebruiker te tonen.
    |
    | De database kan de timestamp technisch nog steeds veilig opslaan
    | terwijl de interface deze correct lokaal weergeeft.
    |
    */

    'timezone' => [

        'capture_browser_timezone' => env(
            'LOGIN_SECURITY_CAPTURE_BROWSER_TIMEZONE',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        |
        | Als de browser geen timezone doorgeeft gebruiken we
        | de Laravel application timezone.
        |
        */

        'fallback' => env(
            'APP_TIMEZONE',
            'Europe/Amsterdam'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Apparaatdetectie
    |--------------------------------------------------------------------------
    |
    | Best-effort detectie op basis van de browser User-Agent.
    |
    | Bijvoorbeeld:
    |
    | - iPhone
    | - iPad
    | - Samsung Android
    | - Windows-pc
    | - Mac
    |
    | Browsers geven niet altijd het exacte hardwaremodel vrij.
    |
    */

    'device' => [

        'enabled' => env(
            'LOGIN_SECURITY_DEVICE_DETECTION_ENABLED',
            true
        ),

        'store_user_agent' => env(
            'LOGIN_SECURITY_STORE_USER_AGENT',
            true
        ),

        'detect_browser' => true,

        'detect_operating_system' => true,

        'detect_device_type' => true,

    ],


    /*
    |--------------------------------------------------------------------------
    | Nieuw apparaat detecteren
    |--------------------------------------------------------------------------
    |
    | Een fingerprint wordt alleen gebruikt om te herkennen of een
    | vergelijkbaar apparaat eerder voor hetzelfde account is gezien.
    |
    | Niet gebruiken als zelfstandig authenticatiemiddel.
    |
    */

    'new_device' => [

        'enabled' => env(
            'LOGIN_SECURITY_NEW_DEVICE_DETECTION',
            true
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Privacy
    |--------------------------------------------------------------------------
    |
    | Centrale instellingen voor welke loginmetadata mag worden bewaard.
    |
    */

    'privacy' => [

        'store_ip' => env(
            'LOGIN_SECURITY_STORE_IP',
            true
        ),

        'store_user_agent' => env(
            'LOGIN_SECURITY_STORE_USER_AGENT',
            true
        ),

        'store_estimated_location' => env(
            'LOGIN_SECURITY_STORE_ESTIMATED_LOCATION',
            true
        ),

        /*
        |--------------------------------------------------------------------------
        | GPS alleen na browsertoestemming
        |--------------------------------------------------------------------------
        */

        'store_precise_location_with_consent' => env(
            'LOGIN_SECURITY_STORE_PRECISE_LOCATION',
            true
        ),

    ],

];