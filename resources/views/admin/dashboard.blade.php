@extends('layouts.admin-layout')
@section('title', 'Mashal Admin | Dashboard')
@section('page-title', 'Dashboard')
@section('content')
{{--
MASHAL ADMIN — EXPERT DASHBOARD
Integratiecontract
1. Plaats dit bestand op de bestaande dashboard-viewlocatie.
2. Behoud layouts.admin-layout en de bestaande named routes.
3. Vereiste routes: users.index, users.create, users.edit, users.destroy,
   home en catalog. De originele routecontracten blijven intact.
4. $users is een Collection of Laravel-paginator met User-modellen.
5. Vereiste User-methoden uit het bronbestand: avatarUrl(), initials(),
   hasProfilePhoto(), socialAvatar(), loginProvider(), loginProviderLabel().
6. De beheerroute moet auth-middleware en serverautorisatie gebruiken.
7. Elke muterende controller moet afzonderlijk zijn policy controleren.
8. Zelfverwijdering en het verwijderen van de laatste beheerder moeten
   server-side worden geblokkeerd; een verborgen knop is geen beveiliging.
9. Aanvullende rapporten zijn optioneel en alleen-lezen.
10. Lever $dashboardData als array met hieronder gedefinieerde modules.
    Per module: ['rows' => [...], 'total' => 123, 'updated_at' => '...'].
11. Elke rij is een array met de zes velden uit de moduleconfiguratie.
    Alle waarden zijn scalair; relaties worden niet vanuit deze view opgehaald.
12. date bevat een ISO-datum of ISO-tijdstip; geldbedragen worden vooraf
    geformatteerd in de controller. Er worden geen valuta aangenomen.
13. Optioneel: sort bevat ruwe scalairen per veld (bijvoorbeeld centen).
    Voorbeeld: ['total' => '€ 1.250,00', 'sort' => ['total' => 125000]].
14. Autoriseer rapporten vóór het aanleveren van data. Lever alleen velden
    die de huidige beheerder mag zien en exporteren; de client ziet alle rijen.
15. Zoekfunctie, filters, grafieken en exports omvatten uitsluitend de
    geladen selectie. Een server-totaal is nadrukkelijk geen lokale rijtelling.
16. Bij een paginator blijft servernavigatie onder het gebruikersblok staan.
17. Er zijn geen fictieve omzetcijfers, klanten of serverstatussen opgenomen.
18. CSV beschermt tekstcellen tegen spreadsheetformules. Controleer dat
    export van de aangeleverde persoonsgegevens past bij je autorisatiebeleid.
19. Alleen thema, dichtheid en moduleweergave worden lokaal bewaard.
    Zoektermen, rijen en persoonsgegevens worden niet in localStorage bewaard.
20. Inline CSS en JavaScript kunnen een CSP-nonce krijgen via $cspNonce.
    De layout en het CSP-headerbeleid moeten dezelfde servernonce gebruiken.
21. Dit bestand vereist geen extra frontendpackage of externe CDN.
22. Test integratie in de eigen Laravel-app; die app is niet meegeleverd.
--}}
@php
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    $dashboardPaginator = isset($users) && $users instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $users
        : null;
    $users = $dashboardPaginator
        ? collect($dashboardPaginator->items())
        : collect($users ?? []);
    $dashboardData = is_array($dashboardData ?? null) ? $dashboardData : [];
    $mxText = static function ($value): string {
        return is_scalar($value) ? (string) $value : '';
    };
    $mxDisplay = static function ($value) use ($mxText): string {
        $text = $mxText($value);
        return $text === '' ? '—' : $text;
    };
    $mxDefinitions = [
        'orders' => [
            'title' => 'Bestellingen',
            'group' => 'Commercie',
            'description' => 'Volg orders van aanvraag tot afronding.',
            'singular' => 'order',
            'fields' => [
                'number' => [
                    'label' => 'Ordernummer',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'total' => [
                    'label' => 'Totaal',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Orderdatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'quotes' => [
            'title' => 'Offertes',
            'group' => 'Commercie',
            'description' => 'Volg offertes en de geldigheid van voorstellen.',
            'singular' => 'offerte',
            'fields' => [
                'number' => [
                    'label' => 'Offertenummer',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'total' => [
                    'label' => 'Offertebedrag',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Geldig tot',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'leads' => [
            'title' => 'Aanvragen',
            'group' => 'Commercie',
            'description' => 'Bekijk geïnteresseerden en toegewezen opvolging.',
            'singular' => 'aanvraag',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Contact',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Interesse',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Eigenaar',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Ontvangen',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'appointments' => [
            'title' => 'Afspraken',
            'group' => 'Planning',
            'description' => 'Bekijk geplande gesprekken, bezichtigingen en afspraken.',
            'singular' => 'afspraak',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Contact',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Onderwerp',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Medewerker',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Afspraakdatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'testdrives' => [
            'title' => 'Proefritten',
            'group' => 'Planning',
            'description' => 'Houd proefritten en de toegewezen voertuigen bij.',
            'singular' => 'proefrit',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Bestuurder',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Begeleider',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Proefritdatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'inventory' => [
            'title' => 'Voorraad',
            'group' => 'Voertuigen',
            'description' => 'Bekijk beschikbare voertuigen en voorraadstatussen.',
            'singular' => 'voertuig',
            'fields' => [
                'number' => [
                    'label' => 'Voorraadnummer',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'registration' => [
                    'label' => 'Kenteken',
                    'type' => 'text', 'numeric' => false,
                ],
                'price' => [
                    'label' => 'Vraagprijs',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Binnengekomen',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'reservations' => [
            'title' => 'Reserveringen',
            'group' => 'Voertuigen',
            'description' => 'Bekijk voertuigreserveringen en vervaldata.',
            'singular' => 'reservering',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Verantwoordelijke',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Vervalt op',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'tradeins' => [
            'title' => 'Inruil',
            'group' => 'Voertuigen',
            'description' => 'Volg inruilaanvragen en de aangeleverde taxatiewaarde.',
            'singular' => 'inruil',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Eigenaar',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Inruilvoertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'valuation' => [
                    'label' => 'Taxatie',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Aangevraagd',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'invoices' => [
            'title' => 'Facturen',
            'group' => 'Financiën',
            'description' => 'Bekijk facturen en openstaande betaalstatussen.',
            'singular' => 'factuur',
            'fields' => [
                'number' => [
                    'label' => 'Factuurnummer',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'reference' => [
                    'label' => 'Orderreferentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'total' => [
                    'label' => 'Bedrag',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Vervaldatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'payments' => [
            'title' => 'Betalingen',
            'group' => 'Financiën',
            'description' => 'Bekijk aangeleverde betaalregistraties en afhandeling.',
            'singular' => 'betaling',
            'fields' => [
                'number' => [
                    'label' => 'Transactie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'method' => [
                    'label' => 'Betaalmethode',
                    'type' => 'text', 'numeric' => false,
                ],
                'total' => [
                    'label' => 'Bedrag',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Betaaldatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'refunds' => [
            'title' => 'Terugbetalingen',
            'group' => 'Financiën',
            'description' => 'Volg geregistreerde terugbetalingen.',
            'singular' => 'terugbetaling',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'reason' => [
                    'label' => 'Reden',
                    'type' => 'text', 'numeric' => false,
                ],
                'total' => [
                    'label' => 'Bedrag',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Aangevraagd',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'deliveries' => [
            'title' => 'Afleveringen',
            'group' => 'Planning',
            'description' => 'Bekijk aflevermomenten en verantwoordelijke medewerkers.',
            'singular' => 'aflevering',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Medewerker',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Afleverdatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'workshop' => [
            'title' => 'Werkplaats',
            'group' => 'Service',
            'description' => 'Volg werkorders en de toegewezen werkplaats.',
            'singular' => 'werkorder',
            'fields' => [
                'number' => [
                    'label' => 'Werkordernummer',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Werkzaamheden',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Monteur',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Gepland',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'warranties' => [
            'title' => 'Garanties',
            'group' => 'Service',
            'description' => 'Bekijk garantiegevallen en hun behandeling.',
            'singular' => 'garantiegeval',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'vehicle' => [
                    'label' => 'Voertuig',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Melding',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Gemeld',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'tickets' => [
            'title' => 'Support',
            'group' => 'Service',
            'description' => 'Bekijk klantvragen en de verantwoordelijke medewerker.',
            'singular' => 'ticket',
            'fields' => [
                'number' => [
                    'label' => 'Ticketnummer',
                    'type' => 'text', 'numeric' => false,
                ],
                'customer' => [
                    'label' => 'Klant',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Onderwerp',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Medewerker',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Aangemaakt',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'tasks' => [
            'title' => 'Taken',
            'group' => 'Planning',
            'description' => 'Houd toegewezen taken en deadlines in beeld.',
            'singular' => 'taak',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Taak',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Eigenaar',
                    'type' => 'text', 'numeric' => false,
                ],
                'priority' => [
                    'label' => 'Prioriteit',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Deadline',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'campaigns' => [
            'title' => 'Campagnes',
            'group' => 'Commercie',
            'description' => 'Bekijk marketingcampagnes en de aangeleverde resultaten.',
            'singular' => 'campagne',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Campagne',
                    'type' => 'text', 'numeric' => false,
                ],
                'channel' => [
                    'label' => 'Kanaal',
                    'type' => 'text', 'numeric' => false,
                ],
                'result' => [
                    'label' => 'Resultaat',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Startdatum',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'imports' => [
            'title' => 'Imports',
            'group' => 'Beheer',
            'description' => 'Controleer importtaken en hun verwerkingsresultaat.',
            'singular' => 'import',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Bronbestand',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Gestart door',
                    'type' => 'text', 'numeric' => false,
                ],
                'result' => [
                    'label' => 'Resultaat',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Gestart',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'exports' => [
            'title' => 'Exports',
            'group' => 'Beheer',
            'description' => 'Bekijk serverexports die door de controller zijn aangeleverd.',
            'singular' => 'export',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Rapport',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Aangevraagd door',
                    'type' => 'text', 'numeric' => false,
                ],
                'result' => [
                    'label' => 'Resultaat',
                    'type' => 'text', 'numeric' => true,
                ],
                'status' => [
                    'label' => 'Status',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Aangevraagd',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
        'audit' => [
            'title' => 'Activiteiten',
            'group' => 'Beheer',
            'description' => 'Bekijk door de server aangeleverde auditgebeurtenissen.',
            'singular' => 'activiteit',
            'fields' => [
                'number' => [
                    'label' => 'Referentie',
                    'type' => 'text', 'numeric' => false,
                ],
                'owner' => [
                    'label' => 'Uitgevoerd door',
                    'type' => 'text', 'numeric' => false,
                ],
                'subject' => [
                    'label' => 'Actie',
                    'type' => 'text', 'numeric' => false,
                ],
                'reference' => [
                    'label' => 'Object',
                    'type' => 'text', 'numeric' => false,
                ],
                'status' => [
                    'label' => 'Resultaat',
                    'type' => 'text', 'numeric' => false,
                ],
                'date' => [
                    'label' => 'Tijdstip',
                    'type' => 'date', 'numeric' => false,
                ],
            ],
        ],
    ];
    $mxModules = [];
    foreach ($mxDefinitions as $mxKey => $mxDefinition) {
        $mxSource = $dashboardData[$mxKey] ?? null;
        $mxConnected = is_array($mxSource) && array_key_exists('rows', $mxSource);
        $mxRows = $mxConnected && is_iterable($mxSource['rows'])
            ? collect($mxSource['rows'])->filter(fn ($row) => is_array($row))->values()
            : collect();
        $mxTotal = $mxConnected && is_numeric($mxSource['total'] ?? null)
            ? max($mxRows->count(), (int) $mxSource['total'])
            : $mxRows->count();
        $mxModules[$mxKey] = [
            'connected' => $mxConnected,
            'rows' => $mxRows,
            'total' => $mxTotal,
            'updated_at' => $mxText($mxSource['updated_at'] ?? ''),
        ];
    }
@endphp

<style nonce="{{ $cspNonce ?? '' }}">
    .mashal-dashboard {
        --m-bg: #08090b;
        --m-panel: #101318;
        --m-panel-soft: #15191f;
        --m-panel-hover: #191e25;
        --m-text: #f6f4ef;
        --m-muted: #8b9199;
        --m-muted-2: #686e76;
        --m-line: rgba(255, 255, 255, .075);
        --m-line-strong: rgba(215, 164, 95, .22);
        --m-gold: #d7a45f;
        --m-gold-light: #f1c983;
        --m-gold-dark: #9c6d34;
        --m-green: #65d59a;
        --m-red: #ef8f8f;
        --m-blue: #8fb6ec;
        --m-shadow: 0 28px 80px rgba(0, 0, 0, .24);
        position: relative;
        min-height: 100%;
        color: var(--m-text);
    }
    .mashal-dashboard,
    .mashal-dashboard * {
        box-sizing: border-box;
    }
    .mashal-dashboard a {
        color: inherit;
    }
    .mashal-dashboard button,
    .mashal-dashboard input {
        font: inherit;
    }
    /* ========================================================= */
    /* HERO                                                       */
    /* ========================================================= */
    .md-hero {
        position: relative;
        overflow: hidden;
        margin-bottom: 22px;
        padding: 34px;
        border: 1px solid var(--m-line);
        border-radius: 28px;
        background:
            radial-gradient(circle at 90% 15%, rgba(215, 164, 95, .14), transparent 18rem),
            linear-gradient(145deg, #11151a, #0c0f13);
        box-shadow: var(--m-shadow);
    }
    .md-hero::after {
        content: "M";
        position: absolute;
        right: -18px;
        bottom: -70px;
        color: rgba(255, 255, 255, .025);
        font-size: clamp(180px, 28vw, 360px);
        line-height: .8;
        font-weight: 950;
        letter-spacing: -.1em;
        pointer-events: none;
    }
    .md-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);
        gap: 34px;
        align-items: end;
    }
    .md-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: var(--m-gold-light);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }
    .md-eyebrow::before {
        content: "";
        width: 28px;
        height: 1px;
        background: var(--m-gold);
    }
    .md-hero h1 {
        max-width: 760px;
        margin: 13px 0 0;
        font-size: clamp(38px, 5.3vw, 72px);
        line-height: .98;
        font-weight: 950;
        letter-spacing: -.06em;
    }
    .md-hero h1 span {
        color: var(--m-gold-light);
    }
    .md-hero-copy {
        max-width: 720px;
        margin: 16px 0 0;
        color: var(--m-muted);
        font-size: 13px;
        line-height: 1.8;
    }
    .md-hero-actions {
        margin-top: 24px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .md-btn {
        min-height: 44px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid transparent;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--m-gold-light), var(--m-gold));
        color: #17110b !important;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .02em;
        text-decoration: none;
        cursor: pointer;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease,
            background .2s ease;
    }
    .md-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(215, 164, 95, .2);
    }
    .md-btn.secondary {
        border-color: var(--m-line);
        background: rgba(255, 255, 255, .035);
        color: #ddd9d1 !important;
    }
    .md-btn.secondary:hover {
        border-color: var(--m-line-strong);
        background: rgba(215, 164, 95, .07);
    }
    .md-btn.danger {
        border-color: rgba(239, 143, 143, .18);
        background: rgba(239, 143, 143, .08);
        color: #efaaaa !important;
    }
    .md-hero-admin {
        padding: 20px;
        border: 1px solid var(--m-line);
        border-radius: 20px;
        background: rgba(255, 255, 255, .025);
        backdrop-filter: blur(12px);
    }
    .md-hero-admin-label {
        color: var(--m-gold-dark);
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }
    .md-hero-admin-name {
        margin-top: 7px;
        color: #fff;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -.035em;
    }
    .md-hero-admin-email {
        margin-top: 5px;
        color: var(--m-muted);
        font-size: 10px;
        line-height: 1.6;
        word-break: break-word;
    }
    .md-admin-badge {
        margin-top: 15px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 10px;
        border: 1px solid rgba(101, 213, 154, .15);
        border-radius: 999px;
        background: rgba(101, 213, 154, .055);
        color: #9ce7bc;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .md-admin-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 14px currentColor;
    }
    /* ========================================================= */
    /* STATS                                                      */
    /* ========================================================= */
    .md-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }
    .md-stat-card {
        position: relative;
        overflow: hidden;
        min-height: 166px;
        padding: 20px;
        border: 1px solid var(--m-line);
        border-radius: 20px;
        background: linear-gradient(145deg, var(--m-panel), #0d1014);
        transition:
            transform .22s ease,
            border-color .22s ease,
            background .22s ease;
    }
    .md-stat-card:hover {
        transform: translateY(-4px);
        border-color: var(--m-line-strong);
        background: linear-gradient(145deg, #14181e, #0f1216);
    }
    .md-stat-card::after {
        content: "";
        position: absolute;
        right: -28px;
        bottom: -42px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(215,164,95,.08), transparent 68%);
    }
    .md-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .md-stat-label {
        color: var(--m-muted);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .md-stat-icon {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 11px;
        background: rgba(215,164,95,.06);
        color: var(--m-gold-light);
        font-size: 10px;
        font-weight: 900;
    }
    .md-stat-number {
        margin-top: 18px;
        color: #fff;
        font-size: 38px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.05em;
    }
    .md-stat-foot {
        margin-top: 9px;
        color: var(--m-muted-2);
        font-size: 10px;
        line-height: 1.55;
    }
    /* ========================================================= */
    /* GENERIC PANEL                                              */
    /* ========================================================= */
    .md-panel {
        margin-bottom: 22px;
        padding: 26px;
        border: 1px solid var(--m-line);
        border-radius: 24px;
        background: linear-gradient(145deg, rgba(17,20,25,.97), rgba(12,15,19,.97));
        box-shadow: 0 22px 60px rgba(0, 0, 0, .14);
    }
    .md-section-head {
        margin-bottom: 22px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 22px;
    }
    .md-section-kicker {
        color: var(--m-gold-dark);
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }
    .md-section-head h2 {
        margin: 7px 0 0;
        color: #fff;
        font-size: 25px;
        line-height: 1.1;
        letter-spacing: -.04em;
    }
    .md-section-head p {
        max-width: 580px;
        margin: 7px 0 0;
        color: var(--m-muted);
        font-size: 11px;
        line-height: 1.7;
    }
    /* ========================================================= */
    /* QUICK ACTIONS                                              */
    /* ========================================================= */
    .md-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }
    .md-action-card {
        min-height: 220px;
        padding: 19px;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--m-line);
        border-radius: 18px;
        background: rgba(255,255,255,.022);
        transition:
            transform .22s ease,
            border-color .22s ease,
            background .22s ease;
    }
    .md-action-card:hover {
        transform: translateY(-4px);
        border-color: var(--m-line-strong);
        background: rgba(215,164,95,.045);
    }
    .md-action-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.15);
        border-radius: 12px;
        color: var(--m-gold-light);
        background: rgba(215,164,95,.055);
        font-size: 11px;
        font-weight: 900;
    }
    .md-action-card h3 {
        margin: 18px 0 0;
        color: #f3f1ec;
        font-size: 16px;
        letter-spacing: -.025em;
    }
    .md-action-card p {
        margin: 8px 0 18px;
        color: var(--m-muted);
        font-size: 10px;
        line-height: 1.7;
    }
    .md-action-card .md-btn {
        margin-top: auto;
        align-self: flex-start;
    }
    /* ========================================================= */
    /* USER TABLE                                                 */
    /* ========================================================= */
    .md-table-tools {
        margin-bottom: 16px;
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        gap: 12px;
        align-items: center;
    }
    .md-search {
        position: relative;
    }
    .md-search input {
        width: 100%;
        min-height: 44px;
        padding: 0 15px 0 39px;
        border: 1px solid var(--m-line);
        border-radius: 13px;
        outline: none;
        background: rgba(255,255,255,.025);
        color: #eeeae3;
        font-size: 11px;
    }
    .md-search input::placeholder {
        color: #626870;
    }
    .md-search input:focus {
        border-color: rgba(215,164,95,.32);
        box-shadow: 0 0 0 3px rgba(215,164,95,.07);
    }
    .md-search::before {
        content: "⌕";
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--m-gold);
        font-size: 17px;
        pointer-events: none;
    }
    .md-filter-bar {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }
    .md-filter {
        min-height: 36px;
        padding: 0 12px;
        border: 1px solid var(--m-line);
        border-radius: 999px;
        background: rgba(255,255,255,.02);
        color: var(--m-muted);
        font-size: 9px;
        font-weight: 800;
        cursor: pointer;
    }
    .md-filter.active,
    .md-filter:hover {
        border-color: rgba(215,164,95,.24);
        background: rgba(215,164,95,.08);
        color: var(--m-gold-light);
    }
    .md-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--m-line);
        border-radius: 18px;
        background: rgba(0,0,0,.09);
    }
    .md-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
    }
    .md-table th {
        padding: 13px 14px;
        border-bottom: 1px solid var(--m-line);
        background: rgba(255,255,255,.025);
        color: #737a83;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .12em;
        text-align: left;
        text-transform: uppercase;
    }
    .md-table td {
        padding: 14px;
        border-bottom: 1px solid rgba(255,255,255,.045);
        color: #c9c7c1;
        font-size: 10px;
        vertical-align: middle;
    }
    .md-table tbody tr {
        transition: background .18s ease;
    }
    .md-table tbody tr:hover {
        background: rgba(215,164,95,.025);
    }
    .md-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .md-user {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 190px;
    }
    .md-avatar {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 12px;
        background: linear-gradient(145deg, rgba(215,164,95,.12), rgba(215,164,95,.035));
        color: var(--m-gold-light);
        font-size: 13px;
        font-weight: 950;
    }
    .md-avatar {
        position: relative;
        overflow: hidden;
        box-shadow:
            inset 0 0 0 1px rgba(255,255,255,.025),
            0 10px 26px rgba(0,0,0,.18);
    }
    .md-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: inherit;
    }
    .md-avatar.has-image {
        background: #111419;
        color: transparent;
    }
    .md-user-meta {
        min-width: 0;
    }
    .md-photo-source {
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #6f757d;
        font-size: 7px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .md-photo-source::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--m-gold);
        box-shadow: 0 0 10px rgba(215,164,95,.35);
    }
    .md-photo-source.custom {
        color: #d4a861;
    }
    .md-photo-source.social {
        color: #8fb6ec;
    }
    .md-hero-admin-profile {
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 13px;
    }
    .md-hero-admin-avatar {
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(215,164,95,.22);
        border-radius: 16px;
        background: linear-gradient(
            145deg,
            rgba(215,164,95,.16),
            rgba(215,164,95,.045)
        );
        color: var(--m-gold-light);
        font-size: 17px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(0,0,0,.18);
    }
    .md-hero-admin-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }
    .md-hero-admin-identity {
        min-width: 0;
    }
    .md-hero-admin-photo-source {
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--m-muted-2);
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .md-hero-admin-photo-source::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--m-gold);
        box-shadow: 0 0 12px rgba(215,164,95,.35);
    }
    .md-user strong {
        display: block;
        color: #f3f1ec;
        font-size: 11px;
    }
    .md-user small {
        display: block;
        margin-top: 3px;
        color: var(--m-muted-2);
        font-size: 8px;
    }
    .md-self {
        margin-top: 4px !important;
        color: var(--m-green) !important;
        font-weight: 900;
    }
    .md-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 9px;
        border: 1px solid var(--m-line);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #b9bdc2;
        font-size: 8px;
        font-weight: 900;
        white-space: nowrap;
    }
    .md-badge.admin {
        border-color: rgba(143,182,236,.16);
        background: rgba(143,182,236,.07);
        color: var(--m-blue);
    }
    .md-badge.verified {
        border-color: rgba(101,213,154,.16);
        background: rgba(101,213,154,.06);
        color: #99e7ba;
    }
    .md-badge.pending {
        border-color: rgba(241,201,131,.16);
        background: rgba(241,201,131,.06);
        color: #edc47d;
    }
    /* ========================================================= */
    /* LOGIN PROVIDER                                            */
    /* ========================================================= */
    .md-provider {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 6px 10px;
        border: 1px solid var(--m-line);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #c9c7c1;
        font-size: 8px;
        font-weight: 900;
        white-space: nowrap;
    }
    .md-provider-icon {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        overflow: hidden;
        font-size: 10px;
        font-weight: 900;
    }
    .md-provider-icon svg {
        width: 14px;
        height: 14px;
        display: block;
    }
    .md-provider.google .md-provider-icon {
        background: #ffffff;
    }
    .md-provider.github .md-provider-icon {
        background: #f4f4f4;
        color: #111111;
    }
    .md-provider.facebook .md-provider-icon {
        background: #1877f2;
        color: #ffffff;
    }
    .md-provider.email_code {
        border-color: rgba(215,164,95,.18);
        background: rgba(215,164,95,.055);
        color: var(--m-gold-light);
    }
    .md-provider.email_code .md-provider-icon {
        border: 1px solid rgba(215,164,95,.22);
        background: rgba(215,164,95,.09);
        color: var(--m-gold-light);
    }
    .md-provider.magic_link {
        border-color: rgba(177,132,255,.18);
        background: rgba(177,132,255,.06);
        color: #c9adff;
    }
    .md-provider.magic_link .md-provider-icon {
        border: 1px solid rgba(177,132,255,.20);
        background: rgba(177,132,255,.09);
        color: #c9adff;
    }
    .md-provider.password {
        border-color: rgba(143,182,236,.15);
        background: rgba(143,182,236,.055);
        color: var(--m-blue);
    }
    .md-provider.password .md-provider-icon {
        border: 1px solid rgba(143,182,236,.18);
        background: rgba(143,182,236,.08);
        color: var(--m-blue);
    }
    .md-current-provider {
        margin-top: 12px;
    }
    .md-row-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }
    .md-row-actions .md-btn {
        min-height: 34px;
        padding: 0 11px;
        font-size: 8px;
    }
    .md-self-lock {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 10px;
        border: 1px solid var(--m-line);
        border-radius: 999px;
        color: var(--m-muted);
        background: rgba(255,255,255,.018);
        font-size: 8px;
        font-weight: 800;
    }
    .md-empty {
        padding: 48px 20px;
        text-align: center;
    }
    .md-empty-mark {
        width: 50px;
        height: 50px;
        margin: 0 auto 14px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 16px;
        background: rgba(215,164,95,.05);
        color: var(--m-gold-light);
        font-weight: 900;
    }
    .md-empty h3 {
        margin: 0;
        color: #eeeae3;
        font-size: 18px;
    }
    .md-empty p {
        max-width: 430px;
        margin: 7px auto 18px;
        color: var(--m-muted);
        font-size: 10px;
        line-height: 1.7;
    }
    .md-table-footer {
        margin-top: 16px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .md-visible-count {
        color: var(--m-muted-2);
        font-size: 9px;
    }
    /* ========================================================= */
    /* ADMIN INFO                                                 */
    /* ========================================================= */
    .md-admin-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }
    .md-info-card {
        padding: 18px;
        border: 1px solid var(--m-line);
        border-radius: 17px;
        background: rgba(255,255,255,.02);
    }
    .md-info-card small {
        display: block;
        color: var(--m-gold-dark);
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .md-info-card strong {
        display: block;
        margin-top: 8px;
        color: #f0eee9;
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
    }
    .md-info-card p {
        margin: 6px 0 0;
        color: var(--m-muted);
        font-size: 10px;
        line-height: 1.65;
    }
    /* ========================================================= */
    /* NO SEARCH RESULTS                                          */
    /* ========================================================= */
    .md-no-results {
        display: none;
        margin-top: 14px;
        padding: 20px;
        border: 1px dashed var(--m-line-strong);
        border-radius: 14px;
        color: var(--m-muted);
        background: rgba(215,164,95,.025);
        font-size: 10px;
        line-height: 1.7;
        text-align: center;
    }
    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */
    @media (max-width: 1180px) {
        .md-stats,
        .md-actions-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 900px) {
        .md-hero-grid,
        .md-admin-grid {
            grid-template-columns: 1fr;
        }
        .md-table-tools {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 680px) {
        .md-hero,
        .md-panel {
            padding: 20px;
            border-radius: 20px;
        }
        .md-stats,
        .md-actions-grid {
            grid-template-columns: 1fr;
        }
        .md-section-head,
        .md-table-footer {
            align-items: stretch;
            flex-direction: column;
        }
        .md-hero-actions {
            flex-direction: column;
        }
        .md-hero-actions .md-btn,
        .md-section-head > .md-btn {
            width: 100%;
        }
    }
    /* Expert workspace: component styles stay scoped to the dashboard. */
    .mashal-dashboard {
        color-scheme: dark;
        --mx-control: #191e25;
        --mx-focus: #f1c983;
        --mx-radius: 14px;
        --mx-gap: 16px;
        --mx-muted: #b5bbc3;
        --mx-foreground: #f6f4ef;
        --mx-background: #101318;
        background: var(--m-bg);
        padding: 16px;
        border-radius: 24px;
    }
    .mashal-dashboard [hidden] {
        display: none !important;
    }
    .mashal-dashboard :is(button, a, input, select, summary):focus-visible {
        outline: 3px solid var(--mx-focus);
        outline-offset: 4px;
    }
    .mashal-dashboard button:disabled {
        cursor: not-allowed;
        opacity: .45;
        transform: none;
        box-shadow: none;
    }
    .mashal-dashboard :is(input, select) {
        min-height: 44px;
        max-width: 100%;
        border: 1px solid var(--m-line);
        border-radius: 10px;
        background: var(--mx-control);
        color: var(--mx-foreground);
        padding: 10px 12px;
        font: inherit;
        font-size: 13px;
    }
    .mashal-dashboard select option {
        background: var(--mx-control);
        color: var(--mx-foreground);
    }
    .mashal-dashboard :is(input, select)[aria-invalid="true"] {
        border-color: var(--m-red);
    }
    .mashal-dashboard .mx-sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .mashal-dashboard .mx-skip {
        position: absolute;
        top: 0;
        left: 16px;
        transform: translateY(-200%);
        padding: 12px 18px;
        background: var(--m-gold-light);
        color: #17110b;
        z-index: 50;
    }
    .mashal-dashboard .mx-skip:focus {
        transform: translateY(0);
    }
    .mashal-dashboard .mx-workspace {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        padding: 10px 0 24px;
    }
    .mashal-dashboard .mx-workspace p {
        margin: 5px 0 0;
        color: var(--mx-muted);
        font-size: 12px;
    }
    .mashal-dashboard .mx-overline {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--m-gold-light);
    }
    .mashal-dashboard .mx-workspace-actions,
    .mashal-dashboard .mx-tool-actions,
    .mashal-dashboard .mx-dialog-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .mashal-dashboard kbd {
        padding: 3px 5px;
        border: 1px solid var(--m-line);
        border-radius: 5px;
        font-size: 10px;
    }
    .mashal-dashboard .mx-alert,
    .mashal-dashboard .mx-connection {
        padding: 16px 20px;
        margin: 0 0 18px;
        border: 1px solid var(--m-line-strong);
        border-radius: var(--mx-radius);
        background: rgba(215, 164, 95, .06);
        color: var(--mx-foreground);
        font-size: 13px;
        line-height: 1.7;
    }
    .mashal-dashboard .mx-alert p {
        margin: 5px 0 0;
        color: var(--mx-muted);
    }
    .mashal-dashboard .mx-alert-danger {
        border-color: var(--m-red);
        background: rgba(239, 143, 143, .06);
    }
    .mashal-dashboard .mx-tools {
        display: flex;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
        margin: 16px 0;
    }
    .mashal-dashboard .mx-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 150px;
    }
    .mashal-dashboard .mx-field label {
        color: var(--mx-muted);
        font-size: 12px;
        font-weight: 700;
    }
    .mashal-dashboard .mx-grow {
        flex: 1 1 260px;
    }
    .mashal-dashboard .mx-grid-notice {
        color: var(--m-red);
        font-size: 13px;
    }
    .mashal-dashboard .mx-grid-notice:empty {
        display: none;
    }
    .mashal-dashboard .mx-pagination {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
        margin: 16px 0;
    }
    .mashal-dashboard .mx-page-label {
        font-size: 12px;
        color: var(--mx-muted);
        padding: 0 8px;
        font-variant-numeric: tabular-nums;
    }
    .mashal-dashboard .md-table th button {
        border: 0;
        padding: 8px 0;
        background: none;
        color: inherit;
        text-align: left;
        font: inherit;
        cursor: pointer;
        display: inline-flex;
        gap: 7px;
        align-items: center;
    }
    .mashal-dashboard .md-table th[aria-sort="ascending"],
    .mashal-dashboard .md-table th[aria-sort="descending"] {
        color: var(--m-gold-light);
    }
    .mashal-dashboard .md-table :is(th, td) {
        font-size: 12px;
    }
    .mashal-dashboard .md-table-wrap {
        overscroll-behavior-x: contain;
        scrollbar-color: var(--m-gold-dark) var(--mx-background);
    }
    .mashal-dashboard .mx-report-table {
        min-width: 880px;
    }
    .mashal-dashboard .mx-date {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .mashal-dashboard .mx-empty {
        text-align: center;
        border: 1px dashed var(--m-line-strong);
        border-radius: var(--mx-radius);
        padding: 28px 16px;
        margin-top: 16px;
    }
    .mashal-dashboard .mx-empty p,
    .mashal-dashboard .mx-muted {
        color: var(--mx-muted);
        font-size: 12px;
        line-height: 1.6;
    }
    .mashal-dashboard .mx-analysis-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .mashal-dashboard .mx-chart-card {
        padding: 22px;
        background: var(--mx-background);
        border: 1px solid var(--m-line);
        border-radius: 18px;
    }
    .mashal-dashboard .mx-chart-card h3 {
        margin: 0 0 18px;
        font-size: 16px;
        color: var(--mx-foreground);
    }
    .mashal-dashboard .mx-chart-card p {
        color: var(--mx-muted);
        font-size: 12px;
        line-height: 1.6;
    }
    .mashal-dashboard .mx-bars {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 14px;
    }
    .mashal-dashboard .mx-bar-label {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 7px;
        font-size: 12px;
        color: var(--mx-muted);
    }
    .mashal-dashboard .mx-bar-track {
        height: 8px;
        border-radius: 99px;
        background: var(--mx-control);
        overflow: hidden;
    }
    .mashal-dashboard .mx-bar-fill {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--m-gold-dark), var(--m-gold-light));
    }
    .mashal-dashboard .mx-module-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    .mashal-dashboard .mx-module-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 20px;
        border: 1px solid var(--m-line);
        border-radius: var(--mx-radius);
        text-decoration: none;
        background: var(--mx-background);
        transition: border-color .2s ease, transform .2s ease;
    }
    .mashal-dashboard .mx-module-card:hover {
        border-color: var(--m-gold);
        transform: translateY(-2px);
    }
    .mashal-dashboard .mx-module-card strong {
        font-size: 17px;
    }
    .mashal-dashboard .mx-module-card > span:not(.mx-overline) {
        color: var(--mx-muted);
        font-size: 12px;
        line-height: 1.6;
    }
    .mashal-dashboard .mx-module-count {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid var(--m-line);
    }
    .mashal-dashboard .mx-check {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: var(--mx-muted);
        min-height: 44px;
    }
    .mashal-dashboard .mx-check input {
        width: 18px;
        height: 18px;
        min-height: 18px;
        accent-color: var(--m-gold);
    }
    .mashal-dashboard .mx-module {
        scroll-margin-top: 24px;
    }
    .mashal-dashboard .mx-module-summary {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        list-style: none;
    }
    .mashal-dashboard .mx-module-summary::-webkit-details-marker {
        display: none;
    }
    .mashal-dashboard .mx-module-summary::after {
        content: '+';
        font-size: 24px;
        color: var(--m-gold-light);
    }
    .mashal-dashboard .mx-module[open] > summary::after {
        content: '−';
    }
    .mashal-dashboard .mx-module-heading {
        display: grid;
        gap: 7px;
        margin-right: auto;
    }
    .mashal-dashboard .mx-module-title {
        font-size: 21px;
        font-weight: 800;
    }
    .mashal-dashboard .mx-module-state,
    .mashal-dashboard .mx-scope {
        font-size: 11px;
        color: var(--mx-muted);
        padding: 8px 12px;
        border: 1px solid var(--m-line);
        border-radius: 99px;
    }
    .mashal-dashboard .mx-module-body {
        border-top: 1px solid var(--m-line);
        margin-top: 22px;
        padding-top: 24px;
    }
    .mashal-dashboard .mx-module-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin: 18px 0;
    }
    .mashal-dashboard .mx-module-meta > div {
        border: 1px solid var(--m-line);
        border-radius: var(--mx-radius);
        padding: 16px;
        background: var(--mx-background);
    }
    .mashal-dashboard .mx-module-meta dt {
        font-size: 11px;
        color: var(--mx-muted);
    }
    .mashal-dashboard .mx-module-meta dd {
        margin: 8px 0 0;
        font-size: 18px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }
    .mashal-dashboard .mx-help {
        border-top: 1px solid var(--m-line);
        padding-top: 16px;
        color: var(--mx-muted);
        font-size: 12px;
        line-height: 1.7;
    }
    .mashal-dashboard .mx-help summary {
        cursor: pointer;
    }
    .mashal-dashboard .mx-dialog {
        width: min(640px, calc(100vw - 32px));
        max-height: min(820px, calc(100dvh - 48px));
        padding: 28px;
        border: 1px solid var(--m-line-strong);
        border-radius: 24px;
        background: var(--mx-background);
        color: var(--mx-foreground);
        box-shadow: 0 30px 100px rgba(0, 0, 0, .5);
        overflow-y: auto;
    }
    .mashal-dashboard .mx-dialog::backdrop {
        background: rgba(0, 0, 0, .72);
        backdrop-filter: blur(4px);
    }
    .mashal-dashboard .mx-dialog h2 {
        margin-top: 0;
        font-size: 26px;
    }
    .mashal-dashboard .mx-dialog p {
        color: var(--mx-muted);
        font-size: 14px;
        line-height: 1.7;
    }
    .mashal-dashboard .mx-detail-list {
        display: grid;
        gap: 12px;
        margin: 24px 0;
    }
    .mashal-dashboard .mx-detail-list > div {
        padding-bottom: 12px;
        border-bottom: 1px solid var(--m-line);
    }
    .mashal-dashboard .mx-detail-list dt {
        color: var(--mx-muted);
        font-size: 12px;
    }
    .mashal-dashboard .mx-detail-list dd {
        margin: 5px 0 0;
        font-size: 15px;
        overflow-wrap: anywhere;
    }
    .mashal-dashboard .mx-command-results {
        display: grid;
        gap: 6px;
        max-height: 50vh;
        overflow-y: auto;
        padding: 4px;
        margin: 16px -4px;
    }
    .mashal-dashboard .mx-command-item {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        border: 1px solid var(--m-line);
        border-radius: 12px;
        padding: 14px;
        color: var(--mx-foreground);
        background: var(--mx-control);
        cursor: pointer;
        text-align: left;
    }
    .mashal-dashboard .mx-command-item span {
        color: var(--mx-muted);
    }
    .mashal-dashboard .mx-toast-region {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 100;
        max-width: min(440px, calc(100vw - 48px));
        pointer-events: none;
    }
    .mashal-dashboard .mx-toast {
        padding: 16px 20px;
        border: 1px solid var(--m-gold);
        border-radius: 14px;
        background: var(--mx-background);
        color: var(--mx-foreground);
        font-size: 13px;
        box-shadow: 0 16px 50px rgba(0, 0, 0, .3);
    }
    .mashal-dashboard .mx-toast-error {
        border-color: var(--m-red);
    }
    .mashal-dashboard .mx-server-pagination {
        margin: 18px 0;
        padding: 16px;
        background: var(--mx-background);
        border-radius: var(--mx-radius);
    }
    .mashal-dashboard[data-density="compact"] .md-table td {
        padding: 8px 10px;
    }
    .mashal-dashboard[data-density="compact"] .md-panel {
        padding: 18px;
        margin-bottom: 14px;
    }
    .mashal-dashboard[data-theme="light"] {
        color-scheme: light;
        --m-bg: #f3f0ea;
        --m-panel: #ffffff;
        --m-panel-soft: #f7f4ee;
        --m-text: #20242b;
        --m-muted: #5c626b;
        --m-muted-2: #626973;
        --m-line: rgba(32, 36, 43, .15);
        --m-line-strong: rgba(142, 96, 35, .4);
        --m-gold-light: #805415;
        --m-gold-dark: #765322;
        --mx-control: #f3f0ea;
        --mx-focus: #805415;
        --mx-muted: #555d68;
        --mx-foreground: #20242b;
        --mx-background: #ffffff;
        color: var(--mx-foreground);
    }
    .mashal-dashboard[data-theme="light"] :is(.md-panel, .md-hero, .md-stat-card, .md-action-card, .md-info-card, .md-hero-admin) {
        background: #ffffff;
    }
    .mashal-dashboard[data-theme="light"] :is(h1, h2, h3, strong, .md-stat-number, .md-hero-admin-name, .md-info-card strong) {
        color: #20242b;
    }
    .mashal-dashboard[data-theme="light"] :is(.md-table td, .md-table th, .md-user-meta small, .md-hero-admin-email, .md-section-head p) {
        color: #555d68;
    }
    .mashal-dashboard[data-theme="light"] .md-btn:not(.secondary):not(.danger) {
        background: #e9bd7b;
    }
    .mashal-dashboard[data-theme="light"] .md-btn.secondary {
        color: #363c45 !important;
        background: #f3f0ea;
    }
    .mashal-dashboard[data-theme="light"] .md-badge {
        color: #444b55;
        background: #eeeae4;
    }
    .mashal-dashboard[data-theme="light"] .md-badge.verified {
        color: #17653c;
        background: #e1f3e7;
    }
    .mashal-dashboard[data-theme="light"] .md-badge.pending,
    .mashal-dashboard[data-theme="light"] .md-btn.danger {
        color: #9a2929 !important;
        background: #fae5e5;
    }
    @media (max-width: 1200px) {
        .mashal-dashboard .mx-module-cards {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 700px) {
        .mashal-dashboard {
            padding: 8px;
        }
        .mashal-dashboard .mx-analysis-grid,
        .mashal-dashboard .mx-module-meta {
            grid-template-columns: 1fr;
        }
        .mashal-dashboard .mx-workspace-actions,
        .mashal-dashboard .mx-tool-actions {
            width: 100%;
        }
        .mashal-dashboard .mx-tools > .mx-field {
            flex: 1 1 100%;
        }
        .mashal-dashboard .mx-module-state {
            max-width: 140px;
            line-height: 1.6;
        }
        .mashal-dashboard .mx-pagination {
            justify-content: flex-start;
        }
    }
    @media (max-width: 440px) {
        .mashal-dashboard .mx-module-cards {
            grid-template-columns: 1fr;
        }
        .mashal-dashboard .mx-page-label {
            width: 100%;
            order: -1;
        }
        .mashal-dashboard .mx-dialog {
            padding: 18px;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .mashal-dashboard *,
        .mashal-dashboard *::before,
        .mashal-dashboard *::after {
            animation: none !important;
            transition: none !important;
            scroll-behavior: auto !important;
        }
    }
    @media (forced-colors: active) {
        .mashal-dashboard .mx-bar-fill {
            background: Highlight;
            forced-color-adjust: none;
        }
        .mashal-dashboard :is(.md-btn, .mx-module-card, .md-panel) {
            border: 1px solid CanvasText;
        }
    }
    @media print {
        .mashal-dashboard {
            color: #000 !important;
            background: #fff !important;
            padding: 0;
        }
        .mashal-dashboard .mx-workspace,
        .mashal-dashboard .mx-tools,
        .mashal-dashboard .mx-pagination,
        .mashal-dashboard .mx-module-index,
        .mashal-dashboard .md-hero-actions,
        .mashal-dashboard .md-row-actions,
        .mashal-dashboard .md-table-tools,
        .mashal-dashboard .mx-help,
        .mashal-dashboard .mx-toast-region,
        .mashal-dashboard dialog,
        .mashal-dashboard .md-table th:last-child,
        .mashal-dashboard .md-table td:last-child {
            display: none !important;
        }
        .mashal-dashboard :is(.md-panel, .md-hero, .md-stat-card, .mx-chart-card) {
            color: #000 !important;
            background: #fff !important;
            box-shadow: none;
            border-color: #bbb;
        }
        .mashal-dashboard :is(p, h1, h2, h3, strong, span, th, td, small, dt, dd) {
            color: #000 !important;
        }
        .mashal-dashboard .md-table-wrap {
            overflow: visible;
        }
        .mashal-dashboard .md-table {
            min-width: 0;
            width: 100%;
        }
        .mashal-dashboard .md-table :is(td, th) {
            font-size: 9px;
            padding: 6px;
            overflow-wrap: anywhere;
        }
        .mashal-dashboard .md-table thead {
            display: table-header-group;
        }
        .mashal-dashboard .md-table tr {
            break-inside: avoid;
        }
        .mashal-dashboard .mx-module:not([open]) {
            display: none;
        }
    }
    .mashal-dashboard[data-theme="light"] .md-search input {
        color: #20242b;
        background: #f3f0ea;
    }
    .mashal-dashboard[data-theme="light"] .md-table td strong {
        color: #20242b !important;
    }
    .mashal-dashboard[data-theme="light"] .md-admin-badge {
        color: #17653c;
        background: #e1f3e7;
    }
    .mashal-dashboard .md-section-head p,
    .mashal-dashboard .md-action-card p,
    .mashal-dashboard .md-info-card p,
    .mashal-dashboard .md-stat-foot {
        font-size: 12px;
        line-height: 1.7;
    }
    .mashal-dashboard .md-filter,
    .mashal-dashboard .md-btn {
        font-size: 12px;
        min-height: 42px;
    }
</style>
@php
    $profilePhotoUsers = $users
        ->filter(fn ($dashboardUser) => $dashboardUser->hasProfilePhoto())
        ->count();
@endphp
<div class="mashal-dashboard" id="mashalDashboard" data-theme="dark" data-density="comfortable">
    <a class="mx-skip" href="#mx-users">Direct naar gebruikers</a>
    <header class="mx-workspace" aria-label="Dashboardinstellingen">
        <div>
            <span class="mx-overline">Mashal • Expert</span>
            <p>Je centrale werkruimte voor automotive beheer</p>
        </div>
        <div class="mx-workspace-actions">
            <button type="button" class="md-btn secondary" data-open-command>
                Snel naar… <kbd>Ctrl K</kbd>
            </button>
            <button type="button" class="md-btn secondary" data-theme-toggle aria-pressed="false">
                Licht thema
            </button>
            <button type="button" class="md-btn secondary" data-density-toggle aria-pressed="false">
                Compacte weergave
            </button>
            <button type="button" class="md-btn secondary" data-print>
                Afdrukken
            </button>
        </div>
    </header>
    @if (session('success'))
        <div class="mx-alert" role="status">
            <strong>Gelukt</strong>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mx-alert mx-alert-danger" role="alert">
            <strong>Actie niet voltooid</strong>
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="mx-alert mx-alert-danger" role="alert" tabindex="-1">
            <strong>Controleer de invoer</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <noscript>
        <div class="mx-alert">
            Alle aangeleverde rijen zijn zichtbaar. Schakel JavaScript in voor
            lokale filters, paginering, grafieken en downloads.
            Gebruikers verwijderen vereist JavaScript voor de bevestigingsstap.
        </div>
    </noscript>
    <p class="mx-connection" data-connection role="status" hidden></p>

    {{-- ========================================================= --}}
    {{-- HERO                                                       --}}
    {{-- ========================================================= --}}
    <section class="md-hero">
        <div class="md-hero-grid">
            <div>
                <span class="md-eyebrow">
                    Mashal Control Center
                </span>
                <h1>
                    Automotive beheer,
                    <span>professioneel geregeld.</span>
                </h1>
                <p class="md-hero-copy">
                    Beheer gebruikers, verificaties en administratorrechten vanuit één centraal dashboard.
                    De bestaande Mashal-catalogus en website zijn direct bereikbaar vanuit deze omgeving.
                </p>
                <div class="md-hero-actions">
                    <a
                        href="{{ route('users.create') }}"
                        class="md-btn"
                    >
                        + Nieuwe gebruiker
                    </a>
                    <a
                        href="{{ route('users.index') }}"
                        class="md-btn secondary"
                    >
                        Gebruikersbeheer
                    </a>
                    <a
                        href="{{ route('home') }}"
                        class="md-btn secondary"
                    >
                        Website bekijken
                    </a>
                </div>
            </div>
            <aside class="md-hero-admin">
                <div class="md-hero-admin-profile">
                    <div class="md-hero-admin-avatar">
                        @if (auth()->user()->avatarUrl())
                            <img
                                src="{{ auth()->user()->avatarUrl() }}"
                                alt="Profielfoto van {{ auth()->user()->name }}"
                            >
                        @else
                            {{ auth()->user()->initials() }}
                        @endif
                    </div>
                    <div class="md-hero-admin-identity">
                        <div class="md-hero-admin-label">
                            Huidige administrator
                        </div>
                        <div class="md-hero-admin-name">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="md-hero-admin-email">
                            {{ auth()->user()->email }}
                        </div>
                        @if (auth()->user()->hasProfilePhoto())
                            <span class="md-hero-admin-photo-source">
                                Eigen profielfoto
                            </span>
                        @elseif (auth()->user()->socialAvatar())
                            <span class="md-hero-admin-photo-source">
                                Social avatar
                            </span>
                        @else
                            <span class="md-hero-admin-photo-source">
                                Initialen
                            </span>
                        @endif
                    </div>
                </div>
                <span class="md-admin-badge">
                    Administrator actief
                </span>
                <div class="md-current-provider">
                    @php
                        $currentProvider = auth()->user()->loginProvider();
                    @endphp
                    <span class="md-provider {{ $currentProvider }}">
                        <span class="md-provider-icon" aria-hidden="true">
                            @switch($currentProvider)
                                @case('google')
                                    <svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                        <path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.483h4.844a4.14 4.14 0 0 1-1.797 2.715v2.258h2.909c1.703-1.568 2.684-3.878 2.684-6.615z"/>
                                        <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.715H.955v2.332A9 9 0 0 0 9 18z"/>
                                        <path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.168.281-1.706V4.962H.955A9 9 0 0 0 0 9c0 1.453.347 2.828.955 4.038l3.008-2.332z"/>
                                        <path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.44 1.346l2.581-2.581C13.463.892 11.426 0 9 0A9 9 0 0 0 .955 4.962l3.008 2.332C4.672 5.164 6.656 3.579 9 3.579z"/>
                                    </svg>
                                    @break
                                @case('github')
                                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 .7a11.5 11.5 0 0 0-3.64 22.41c.58.11.79-.25.79-.56v-2.2c-3.22.7-3.9-1.37-3.9-1.37-.52-1.34-1.29-1.69-1.29-1.69-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.72 1.27 3.38.97.1-.75.41-1.27.74-1.56-2.57-.29-5.27-1.29-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.16 1.18A10.97 10.97 0 0 1 12 6.17c.98 0 1.96.13 2.87.39 2.19-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.08 0 4.41-2.71 5.38-5.29 5.67.42.36.79 1.07.79 2.16v3.21c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z"/>
                                    </svg>
                                    @break
                                @case('facebook')
                                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13.6 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.7 1.8-1.7h1.9V2.5c-.3 0-1.5-.1-2.8-.1-2.8 0-4.7 1.7-4.7 4.8v2.3H7v3.5h2.8v9h3.8Z"/>
                                    </svg>
                                    @break
                                @case('email_code')
                                    ✉
                                    @break
                                @case('magic_link')
                                    ↗
                                    @break
                                @default
                                    🔒
                            @endswitch
                        </span>
                        {{ auth()->user()->loginProviderLabel() }}
                    </span>
                </div>
            </aside>
        </div>
    </section>
    {{-- ========================================================= --}}
    {{-- STATISTICS                                                 --}}
    {{-- ========================================================= --}}
    <section class="md-stats">
        <article class="md-stat-card">
            <div class="md-stat-top">
                <span class="md-stat-label">
                    Gebruikers
                </span>
                <span class="md-stat-icon">
                    U
                </span>
            </div>
            <div class="md-stat-number">
                {{ $totalUsers ?? $users->count() }}
            </div>
            <div class="md-stat-foot">
                {{ isset($totalUsers) ? 'Totaal geregistreerde accounts' : 'Accounts in de geladen selectie' }}
            </div>
        </article>
        <article class="md-stat-card">
            <div class="md-stat-top">
                <span class="md-stat-label">
                    Geverifieerd
                </span>
                <span class="md-stat-icon">
                    ✓
                </span>
            </div>
            <div class="md-stat-number">
                {{ $verifiedUsers ?? $users->whereNotNull('email_verified_at')->count() }}
            </div>
            <div class="md-stat-foot">
                {{ isset($verifiedUsers) ? 'Accounts met bevestigd e-mailadres' : 'Geverifieerd in de geladen selectie' }}
            </div>
        </article>
        <article class="md-stat-card">
            <div class="md-stat-top">
                <span class="md-stat-label">
                    Administrators
                </span>
                <span class="md-stat-icon">
                    A
                </span>
            </div>
            <div class="md-stat-number">
                {{ $adminUsers ?? $users->where('is_admin', true)->count() }}
            </div>
            <div class="md-stat-foot">
                {{ isset($adminUsers) ? 'Accounts met beheerrechten' : 'Beheerders in de geladen selectie' }}
            </div>
        </article>
        <article class="md-stat-card">
            <div class="md-stat-top">
                <span class="md-stat-label">
                    Bestellingen
                </span>
                <span class="md-stat-icon">
                    O
                </span>
            </div>
            <div class="md-stat-number">
                {{ $totalOrders ?? '—' }}
            </div>
            <div class="md-stat-foot">
                Totaal geplaatste bestellingen
            </div>
        </article>
        <article class="md-stat-card">
            <div class="md-stat-top">
                <span class="md-stat-label">
                    Profielfoto's
                </span>
                <span class="md-stat-icon">
                    P
                </span>
            </div>
            <div class="md-stat-number">
                {{ $profilePhotoUsers }}
            </div>
            <div class="md-stat-foot">
                Geladen accounts met een eigen geüploade profielfoto
            </div>
        </article>
    </section>
    {{-- ========================================================= --}}
    {{-- QUICK ACTIONS                                               --}}
    {{-- ========================================================= --}}
    <section class="md-panel">
        <div class="md-section-head">
            <div>
                <span class="md-section-kicker">
                    Quick actions
                </span>
                <h2>
                    Snel beheren
                </h2>
                <p>
                    Open direct de onderdelen die je het vaakst nodig hebt binnen Mashal.
                </p>
            </div>
        </div>
        <div class="md-actions-grid">
            <article class="md-action-card">
                <span class="md-action-icon">
                    U
                </span>
                <h3>
                    Gebruikers beheren
                </h3>
                <p>
                    Bekijk bestaande accounts, wijzig accountgegevens
                    en beheer verificatie- en administratorstatussen.
                </p>
                <a
                    class="md-btn secondary"
                    href="{{ route('users.index') }}"
                >
                    Naar gebruikers
                </a>
            </article>
            <article class="md-action-card">
                <span class="md-action-icon">
                    +
                </span>
                <h3>
                    Nieuwe gebruiker
                </h3>
                <p>
                    Maak handmatig een nieuw gebruikers-
                    of administratoraccount aan.
                </p>
                <a
                    class="md-btn"
                    href="{{ route('users.create') }}"
                >
                    Gebruiker toevoegen
                </a>
            </article>
            <article class="md-action-card">
                <span class="md-action-icon">
                    C
                </span>
                <h3>
                    Catalogus bekijken
                </h3>
                <p>
                    Open de actuele Mashal Automotive-collectie
                    zoals deze op de website beschikbaar is.
                </p>
                <a
                    class="md-btn secondary"
                    href="{{ route('catalog') }}"
                >
                    Naar catalogus
                </a>
            </article>
            <article class="md-action-card">
                <span class="md-action-icon">
                    ↗
                </span>
                <h3>
                    Website openen
                </h3>
                <p>
                    Bekijk de publieke Mashal-website
                    zoals bezoekers en klanten die ervaren.
                </p>
                <a
                    class="md-btn secondary"
                    href="{{ route('home') }}"
                >
                    Naar website
                </a>
            </article>
        </div>
    </section>
    {{-- ========================================================= --}}
    {{-- USERS OVERVIEW                                              --}}
    {{-- ========================================================= --}}
    <section class="md-panel mx-user-panel" id="mx-users" data-grid="users" aria-labelledby="mx-users-title">
        <div class="md-section-head">
            <div>
                <span class="md-section-kicker">
                    User management
                </span>
                <h2 id="mx-users-title">
                    Gebruikersoverzicht
                </h2>
                <p>
                    Controleer accounts, rollen, verificatiestatus
                    en registratiedatum vanuit één overzicht.
                </p>
            </div>
            <a
                class="md-btn"
                href="{{ route('users.create') }}"
            >
                + Gebruiker toevoegen
            </a>
        </div>
        @if ($users->isNotEmpty())
            <div class="md-table-tools">
                <div class="md-search">
                    <input id="dashboardUserSearch" data-grid-search aria-label="Zoek in geladen gebruikers" type="search" placeholder="Zoek op naam, e-mailadres, ID of loginmethode..." autocomplete="off" >
                </div>
                <div class="md-filter-bar">
                    <button class="md-filter active" type="button" data-filter="all" >
                        Alle
                    </button>
                    <button class="md-filter" type="button" data-filter="verified" >
                        Geverifieerd
                    </button>
                    <button class="md-filter" type="button" data-filter="pending" >
                        Niet geverifieerd
                    </button>
                    <button class="md-filter" type="button" data-filter="admin" >
                        Administrators
                    </button>
                    <button class="md-filter" type="button" data-filter="google">
                        Google
                    </button>
                    <button class="md-filter" type="button" data-filter="github">
                        GitHub
                    </button>
                    <button class="md-filter" type="button" data-filter="facebook">
                        Facebook
                    </button>
                    <button class="md-filter" type="button" data-filter="email_code">
                        E-mailcode
                    </button>
                    <button class="md-filter" type="button" data-filter="magic_link">
                        Magic link
                    </button>
                    <button class="md-filter" type="button" data-filter="password">
                        Wachtwoord
                    </button>
                    <button class="md-filter" type="button" data-filter="photo">
                        Met profielfoto
                    </button>
                    <button class="md-filter" type="button" data-filter="social_avatar">
                        Social avatar
                    </button>
                </div>
            </div>
        @endif
                <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-users-size">Rijen per pagina</label>
                <select id="mx-users-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-users-from">Vanaf datum</label>
                <input id="mx-users-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-users-to">Tot en met datum</label>
                <input id="mx-users-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering users">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
        <div class="md-table-wrap" tabindex="0" role="region" aria-label="Gebruikerstabel">
            <table class="md-table">
                <caption class="mx-sr-only">Geladen gebruikers met accountacties</caption>
                <thead>
                    <tr>
                        <th scope="col" aria-sort="none"><button type="button" data-sort="0">Gebruiker<span aria-hidden="true"> ↕</span></button></th>
                        <th scope="col" aria-sort="none"><button type="button" data-sort="1">E-mailadres<span aria-hidden="true"> ↕</span></button></th>
                        <th scope="col" aria-sort="none"><button type="button" data-sort="2">Login via<span aria-hidden="true"> ↕</span></button></th>
                        <th scope="col" aria-sort="none"><button type="button" data-sort="3">Rol<span aria-hidden="true"> ↕</span></button></th>
                        <th scope="col" aria-sort="none"><button type="button" data-sort="4">Verificatie<span aria-hidden="true"> ↕</span></button></th>
                        <th scope="col" aria-sort="none"><button type="button" data-sort="5">Toegevoegd<span aria-hidden="true"> ↕</span></button></th>
                        <th scope="col">Acties</th>
                    </tr>
                </thead>
                <tbody id="dashboardUsersBody">
                    @forelse ($users as $user)
                        <tr
                            class="dashboard-user-row"
                            data-grid-row
                            data-created="{{ $user->created_at?->format('Y-m-d') }}"
                            data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}"
                            data-id="{{ $user->id }}"
                            data-admin="{{ $user->is_admin ? '1' : '0' }}"
                            data-verified="{{ $user->email_verified_at ? '1' : '0' }}"
                            data-provider="{{ $user->loginProvider() }}"
                            data-photo="{{ $user->hasProfilePhoto() ? '1' : '0' }}"
                            data-social-avatar="{{ (! $user->hasProfilePhoto() && $user->socialAvatar()) ? '1' : '0' }}"
                        >
                            {{-- USER --}}
                            <td>
                                <div class="md-user">
                                    @if ($user->avatarUrl())
                                        <span class="md-avatar has-image">
                                            <img
                                                src="{{ $user->avatarUrl() }}"
                                                alt="Profielfoto van {{ $user->name }}"
                                                loading="lazy"
                                            >
                                        </span>
                                    @else
                                        <span class="md-avatar">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif
                                    <div class="md-user-meta">
                                        <strong>
                                            {{ $user->name }}
                                        </strong>
                                        <small>
                                            ID #{{ $user->id }}
                                        </small>
                                        @if ($user->hasProfilePhoto())
                                            <span class="md-photo-source custom">
                                                Eigen profielfoto
                                            </span>
                                        @elseif ($user->socialAvatar())
                                            <span class="md-photo-source social">
                                                Social avatar
                                            </span>
                                        @else
                                            <span class="md-photo-source">
                                                Initialen
                                            </span>
                                        @endif
                                        @if (auth()->id() === $user->id)
                                            <small class="md-self">
                                                Dit ben jij
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            {{-- EMAIL --}}
                            <td style=" word-break: break-word; " >
                                {{ $user->email }}
                            </td>
                            {{-- LOGIN PROVIDER --}}
                            <td>
                                @php
                                    $provider = $user->loginProvider();
                                @endphp
                                <span
                                    class="md-provider {{ $provider }}"
                                    title="Laatste login via {{ $user->loginProviderLabel() }}"
                                >
                                    <span class="md-provider-icon" aria-hidden="true">
                                        @switch($provider)
                                            @case('google')
                                                <svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.483h4.844a4.14 4.14 0 0 1-1.797 2.715v2.258h2.909c1.703-1.568 2.684-3.878 2.684-6.615z"/>
                                                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.715H.955v2.332A9 9 0 0 0 9 18z"/>
                                                    <path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.168.281-1.706V4.962H.955A9 9 0 0 0 0 9c0 1.453.347 2.828.955 4.038l3.008-2.332z"/>
                                                    <path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.44 1.346l2.581-2.581C13.463.892 11.426 0 9 0A9 9 0 0 0 .955 4.962l3.008 2.332C4.672 5.164 6.656 3.579 9 3.579z"/>
                                                </svg>
                                                @break
                                            @case('github')
                                                <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 .7a11.5 11.5 0 0 0-3.64 22.41c.58.11.79-.25.79-.56v-2.2c-3.22.7-3.9-1.37-3.9-1.37-.52-1.34-1.29-1.69-1.29-1.69-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.72 1.27 3.38.97.1-.75.41-1.27.74-1.56-2.57-.29-5.27-1.29-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.16 1.18A10.97 10.97 0 0 1 12 6.17c.98 0 1.96.13 2.87.39 2.19-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.08 0 4.41-2.71 5.38-5.29 5.67.42.36.79 1.07.79 2.16v3.21c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z"/>
                                                </svg>
                                                @break
                                            @case('facebook')
                                                <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.6 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.7 1.8-1.7h1.9V2.5c-.3 0-1.5-.1-2.8-.1-2.8 0-4.7 1.7-4.7 4.8v2.3H7v3.5h2.8v9h3.8Z"/>
                                                </svg>
                                                @break
                                            @case('email_code')
                                                ✉
                                                @break
                                            @case('magic_link')
                                                ↗
                                                @break
                                            @default
                                                🔒
                                        @endswitch
                                    </span>
                                    {{ $user->loginProviderLabel() }}
                                </span>
                            </td>
                            {{-- ROLE --}}
                            <td>
                                @if ($user->is_admin)
                                    <span class="md-badge admin">
                                        Administrator
                                    </span>
                                @else
                                    <span class="md-badge">
                                        Gebruiker
                                    </span>
                                @endif
                            </td>
                            {{-- VERIFICATION --}}
                            <td>
                                @if ($user->email_verified_at)
                                    <span class="md-badge verified">
                                        ✓ Geverifieerd
                                    </span>
                                @else
                                    <span class="md-badge pending">
                                        Niet geverifieerd
                                    </span>
                                @endif
                            </td>
                            {{-- CREATED --}}
                            <td>
                                <strong
                                    style="
                                        display: block;
                                        color: #e5e2dc;
                                        font-size: 10px;
                                    "
                                >
                                    {{ $user->created_at?->format('d-m-Y') }}
                                </strong>
                                <small
                                    style="
                                        display: block;
                                        margin-top: 4px;
                                        color: var(--m-muted-2);
                                        font-size: 8px;
                                    "
                                >
                                    {{ $user->created_at?->format('H:i') }}
                                </small>
                            </td>
                            {{-- ACTIONS --}}
                            <td>
                                <div class="md-row-actions">
                                    <button type="button" class="md-btn secondary" data-detail>Details</button>
                                    <a
                                        class="md-btn secondary"
                                        href="{{ route('users.edit', $user) }}"
                                    >
                                        Wijzigen
                                    </a>
                                    @if (auth()->id() !== $user->id)
                                        <form
                                            method="POST"
                                            action="{{ route('users.destroy', $user) }}"
                                            style="margin: 0;"
                                            data-confirm-delete="{{ $user->name }}"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button class="md-btn danger" type="submit" disabled >
                                                Verwijderen
                                            </button>
                                        </form>
                                    @else
                                        <span class="md-self-lock">
                                            Eigen account
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="md-empty">
                                    <div class="md-empty-mark">
                                        U
                                    </div>
                                    <h3>
                                        Nog geen gebruikers
                                    </h3>
                                    <p>
                                        Er zijn momenteel nog geen gebruikers geregistreerd.
                                        Maak het eerste account aan om te beginnen.
                                    </p>
                                    <a
                                        class="md-btn"
                                        href="{{ route('users.create') }}"
                                    >
                                        + Eerste gebruiker aanmaken
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->isNotEmpty())
            <div
                id="dashboardNoResults"
                data-grid-empty
                role="status"
                class="md-no-results"
            >
                Geen gebruikers gevonden voor deze zoekopdracht of filter.
            </div>
            <div class="md-table-footer">
                <span
                    id="dashboardVisibleCount"
                    data-grid-summary
                    role="status"
                    class="md-visible-count"
                >
                    {{ $users->count() }} gebruikers zichtbaar
                </span>
                <a
                    class="md-btn secondary"
                    href="{{ route('users.index') }}"
                >
                    Volledig gebruikersbeheer
                </a>
            </div>
        @endif
    </section>
    @if ($dashboardPaginator && method_exists($dashboardPaginator, 'links'))
        <nav class="mx-server-pagination" aria-label="Serverpagina’s gebruikers">
            {{ $dashboardPaginator->links() }}
        </nav>
    @endif
    {{-- ========================================================= --}}
    {{-- ADMINISTRATOR INFORMATION                                  --}}
    {{-- ========================================================= --}}
    <section class="md-panel">
        <div class="md-section-head">
            <div>
                <span class="md-section-kicker">
                    Administrator profile
                </span>
                <h2>
                    Jouw beheerderssessie
                </h2>
                <p>
                    Controleer met welk account je bent ingelogd
                    voordat je gevoelige gebruikerswijzigingen uitvoert.
                </p>
            </div>
        </div>
        <div class="md-admin-grid">
            <article class="md-info-card">
                <small>
                    Ingelogd als
                </small>
                <strong>
                    {{ auth()->user()->name }}
                </strong>
                <p>
                    Actieve beheerder van deze sessie.
                </p>
            </article>
            <article class="md-info-card">
                <small>
                    E-mailadres
                </small>
                <strong>
                    {{ auth()->user()->email }}
                </strong>
                <p>
                    Gekoppeld aan je administratoraccount.
                </p>
            </article>
            <article class="md-info-card">
                <small>
                    Rechten
                </small>
                <strong>
                    Volledige administratorrechten
                </strong>
                <p>
                    Je kunt gebruikers bekijken, wijzigen, aanmaken en verwijderen.
                </p>
            </article>
            <article class="md-info-card">
                <small>
                    Ingelogd via
                </small>
                <strong>
                    {{ auth()->user()->loginProviderLabel() }}
                </strong>
                <p>
                    Laatst gebruikte authenticatiemethode voor deze beheerder.
                </p>
            </article>
        </div>
    </section>
    <section class="md-panel mx-analytics" aria-labelledby="mx-analysis-title">
        <div class="md-section-head">
            <div>
                <span class="md-section-kicker">Selectieanalyse</span>
                <h2 id="mx-analysis-title">Accounts in beeld</h2>
                <p>Deze analyse gebruikt alle geladen gebruikers, onafhankelijk van lokale tabel filters.</p>
            </div>
        </div>
        <div class="mx-analysis-grid">
            <article class="mx-chart-card">
                <h3>Verificatie</h3>
                <div data-chart="verification"></div>
                <p>Verdeling van wel en niet geverifieerde e-mailadressen.</p>
            </article>
            <article class="mx-chart-card">
                <h3>Loginmethoden</h3>
                <div data-chart="providers"></div>
                <p>Laatst geregistreerde loginmethode per geladen account.</p>
            </article>
            <article class="mx-chart-card">
                <h3>Rollen</h3>
                <div data-chart="roles"></div>
                <p>Beheeraccounts en standaardgebruikers in de selectie.</p>
            </article>
            <article class="mx-chart-card">
                <h3>Registraties per maand</h3>
                <div data-chart="registrations"></div>
                <p>De meest recente twaalf maanden die in de geladen selectie voorkomen.</p>
            </article>
        </div>
    </section>
    <section class="md-panel mx-module-index" aria-labelledby="mx-modules-title">
        <div class="md-section-head">
            <div>
                <span class="md-section-kicker">Werkgebieden</span>
                <h2 id="mx-modules-title">Beheeroverzichten</h2>
                <p>Open een overzicht om de aangeleverde gegevens te doorzoeken of te exporteren.</p>
            </div>
        </div>
        <div class="mx-tools">
            <div class="mx-field mx-grow">
                <label for="mx-module-search">Zoek een werkgebied</label>
                <input id="mx-module-search" type="search" placeholder="Bijvoorbeeld voorraad of facturen" autocomplete="off">
            </div>
            <div class="mx-field">
                <label for="mx-module-group">Categorie</label>
                <select id="mx-module-group">
                    <option value="">Alle categorieën</option>
                    <option value="Commercie">Commercie</option>
                    <option value="Planning">Planning</option>
                    <option value="Voertuigen">Voertuigen</option>
                    <option value="Financiën">Financiën</option>
                    <option value="Service">Service</option>
                    <option value="Beheer">Beheer</option>
                </select>
            </div>
            <label class="mx-check">
                <input type="checkbox" id="mx-connected-only">
                Alleen aangesloten overzichten
            </label>
        </div>
        <div class="mx-module-cards">
            @foreach ($mxDefinitions as $mxKey => $mxDefinition)
                <a
                    class="mx-module-card"
                    href="#mx-module-{{ $mxKey }}"
                    data-module-link="{{ $mxKey }}"
                    data-module-title="{{ $mxDefinition['title'] }}"
                    data-module-group="{{ $mxDefinition['group'] }}"
                    data-module-connected="{{ $mxModules[$mxKey]['connected'] ? '1' : '0' }}"
                >
                    <span class="mx-overline">{{ $mxDefinition['group'] }}</span>
                    <strong>{{ $mxDefinition['title'] }}</strong>
                    <span>{{ $mxDefinition['description'] }}</span>
                    <span class="mx-module-count">
                        {{ $mxModules[$mxKey]['connected'] ? $mxModules[$mxKey]['rows']->count().' geladen' : 'Nog niet aangesloten' }}
                    </span>
                </a>
            @endforeach
        </div>
        <p data-module-empty role="status" hidden>Geen werkgebieden gevonden. Pas je zoekopdracht aan.</p>
    </section>
    {-- BESTELLINGEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-orders"
        data-module="orders"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Commercie</span>
                <span class="mx-module-title">Bestellingen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['orders']['connected'] ? $mxModules['orders']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="orders" aria-labelledby="mx-orders-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-orders-heading">Bestellingen</h2>
                    <p>Volg orders van aanvraag tot afronding.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['orders']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['orders']['connected'] ? $mxModules['orders']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['orders']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['orders']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor bestellingen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['orders']['total'] > $mxModules['orders']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-orders-search">Zoek in bestellingen</label>
                    <input id="mx-orders-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-orders-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-orders-status">Status of resultaat</label>
                    <select id="mx-orders-status" data-status-filter aria-controls="mx-orders-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-orders-size">Rijen per pagina</label>
                <select id="mx-orders-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-orders-from">Vanaf datum</label>
                <input id="mx-orders-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-orders-to">Tot en met datum</label>
                <input id="mx-orders-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering orders">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel bestellingen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Bestellingen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op ordernummer" >
                                    Ordernummer
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="total" aria-label="Sorteer op totaal" >
                                    Totaal
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op orderdatum" >
                                    Orderdatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-orders-body">
                        @forelse ($mxModules['orders']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Ordernummer" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="total" data-label="Totaal" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.total', $mxRow['total'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['total'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Orderdatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van order {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen bestellingen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['orders']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één order.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- OFFERTES: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-quotes"
        data-module="quotes"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Commercie</span>
                <span class="mx-module-title">Offertes</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['quotes']['connected'] ? $mxModules['quotes']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="quotes" aria-labelledby="mx-quotes-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-quotes-heading">Offertes</h2>
                    <p>Volg offertes en de geldigheid van voorstellen.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['quotes']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['quotes']['connected'] ? $mxModules['quotes']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['quotes']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['quotes']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor offertes beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['quotes']['total'] > $mxModules['quotes']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-quotes-search">Zoek in offertes</label>
                    <input id="mx-quotes-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-quotes-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-quotes-status">Status of resultaat</label>
                    <select id="mx-quotes-status" data-status-filter aria-controls="mx-quotes-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-quotes-size">Rijen per pagina</label>
                <select id="mx-quotes-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-quotes-from">Vanaf datum</label>
                <input id="mx-quotes-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-quotes-to">Tot en met datum</label>
                <input id="mx-quotes-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering quotes">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel offertes"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Offertes: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op offertenummer" >
                                    Offertenummer
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="total" aria-label="Sorteer op offertebedrag" >
                                    Offertebedrag
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op geldig tot" >
                                    Geldig tot
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-quotes-body">
                        @forelse ($mxModules['quotes']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Offertenummer" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="total" data-label="Offertebedrag" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.total', $mxRow['total'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['total'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Geldig tot" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van offerte {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen offertes geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['quotes']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één offerte.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- AANVRAGEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-leads"
        data-module="leads"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Commercie</span>
                <span class="mx-module-title">Aanvragen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['leads']['connected'] ? $mxModules['leads']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="leads" aria-labelledby="mx-leads-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-leads-heading">Aanvragen</h2>
                    <p>Bekijk geïnteresseerden en toegewezen opvolging.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['leads']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['leads']['connected'] ? $mxModules['leads']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['leads']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['leads']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor aanvragen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['leads']['total'] > $mxModules['leads']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-leads-search">Zoek in aanvragen</label>
                    <input id="mx-leads-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-leads-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-leads-status">Status of resultaat</label>
                    <select id="mx-leads-status" data-status-filter aria-controls="mx-leads-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-leads-size">Rijen per pagina</label>
                <select id="mx-leads-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-leads-from">Vanaf datum</label>
                <input id="mx-leads-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-leads-to">Tot en met datum</label>
                <input id="mx-leads-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering leads">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel aanvragen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Aanvragen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op contact" >
                                    Contact
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op interesse" >
                                    Interesse
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op eigenaar" >
                                    Eigenaar
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op ontvangen" >
                                    Ontvangen
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-leads-body">
                        @forelse ($mxModules['leads']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Contact" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Interesse" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Eigenaar" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Ontvangen" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van aanvraag {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen aanvragen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['leads']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één aanvraag.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- AFSPRAKEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-appointments"
        data-module="appointments"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Planning</span>
                <span class="mx-module-title">Afspraken</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['appointments']['connected'] ? $mxModules['appointments']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="appointments" aria-labelledby="mx-appointments-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-appointments-heading">Afspraken</h2>
                    <p>Bekijk geplande gesprekken, bezichtigingen en afspraken.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['appointments']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['appointments']['connected'] ? $mxModules['appointments']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['appointments']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['appointments']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor afspraken beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['appointments']['total'] > $mxModules['appointments']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-appointments-search">Zoek in afspraken</label>
                    <input id="mx-appointments-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-appointments-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-appointments-status">Status of resultaat</label>
                    <select id="mx-appointments-status" data-status-filter aria-controls="mx-appointments-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-appointments-size">Rijen per pagina</label>
                <select id="mx-appointments-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-appointments-from">Vanaf datum</label>
                <input id="mx-appointments-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-appointments-to">Tot en met datum</label>
                <input id="mx-appointments-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering appointments">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel afspraken"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Afspraken: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op contact" >
                                    Contact
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="subject" aria-label="Sorteer op onderwerp" >
                                    Onderwerp
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op medewerker" >
                                    Medewerker
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op afspraakdatum" >
                                    Afspraakdatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-appointments-body">
                        @forelse ($mxModules['appointments']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Contact" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Onderwerp" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Medewerker" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Afspraakdatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van afspraak {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen afspraken geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['appointments']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één afspraak.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- PROEFRITTEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-testdrives"
        data-module="testdrives"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Planning</span>
                <span class="mx-module-title">Proefritten</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['testdrives']['connected'] ? $mxModules['testdrives']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="testdrives" aria-labelledby="mx-testdrives-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-testdrives-heading">Proefritten</h2>
                    <p>Houd proefritten en de toegewezen voertuigen bij.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['testdrives']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['testdrives']['connected'] ? $mxModules['testdrives']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['testdrives']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['testdrives']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor proefritten beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['testdrives']['total'] > $mxModules['testdrives']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-testdrives-search">Zoek in proefritten</label>
                    <input id="mx-testdrives-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-testdrives-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-testdrives-status">Status of resultaat</label>
                    <select id="mx-testdrives-status" data-status-filter aria-controls="mx-testdrives-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-testdrives-size">Rijen per pagina</label>
                <select id="mx-testdrives-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-testdrives-from">Vanaf datum</label>
                <input id="mx-testdrives-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-testdrives-to">Tot en met datum</label>
                <input id="mx-testdrives-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering testdrives">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel proefritten"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Proefritten: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op bestuurder" >
                                    Bestuurder
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op begeleider" >
                                    Begeleider
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op proefritdatum" >
                                    Proefritdatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-testdrives-body">
                        @forelse ($mxModules['testdrives']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Bestuurder" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Begeleider" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Proefritdatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van proefrit {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen proefritten geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['testdrives']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één proefrit.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- VOORRAAD: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-inventory"
        data-module="inventory"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Voertuigen</span>
                <span class="mx-module-title">Voorraad</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['inventory']['connected'] ? $mxModules['inventory']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="inventory" aria-labelledby="mx-inventory-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-inventory-heading">Voorraad</h2>
                    <p>Bekijk beschikbare voertuigen en voorraadstatussen.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['inventory']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['inventory']['connected'] ? $mxModules['inventory']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['inventory']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['inventory']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor voorraad beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['inventory']['total'] > $mxModules['inventory']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-inventory-search">Zoek in voorraad</label>
                    <input id="mx-inventory-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-inventory-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-inventory-status">Status of resultaat</label>
                    <select id="mx-inventory-status" data-status-filter aria-controls="mx-inventory-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-inventory-size">Rijen per pagina</label>
                <select id="mx-inventory-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-inventory-from">Vanaf datum</label>
                <input id="mx-inventory-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-inventory-to">Tot en met datum</label>
                <input id="mx-inventory-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering inventory">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel voorraad"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Voorraad: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op voorraadnummer" >
                                    Voorraadnummer
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="registration" aria-label="Sorteer op kenteken" >
                                    Kenteken
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="price" aria-label="Sorteer op vraagprijs" >
                                    Vraagprijs
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op binnengekomen" >
                                    Binnengekomen
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-inventory-body">
                        @forelse ($mxModules['inventory']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Voorraadnummer" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="registration" data-label="Kenteken" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.registration', $mxRow['registration'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['registration'] ?? '') }}
                                </td>
                                <td data-field="price" data-label="Vraagprijs" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.price', $mxRow['price'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['price'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Binnengekomen" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van voertuig {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen voorraad geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['inventory']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één voertuig.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- RESERVERINGEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-reservations"
        data-module="reservations"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Voertuigen</span>
                <span class="mx-module-title">Reserveringen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['reservations']['connected'] ? $mxModules['reservations']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="reservations" aria-labelledby="mx-reservations-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-reservations-heading">Reserveringen</h2>
                    <p>Bekijk voertuigreserveringen en vervaldata.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['reservations']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['reservations']['connected'] ? $mxModules['reservations']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['reservations']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['reservations']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor reserveringen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['reservations']['total'] > $mxModules['reservations']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-reservations-search">Zoek in reserveringen</label>
                    <input id="mx-reservations-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-reservations-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-reservations-status">Status of resultaat</label>
                    <select id="mx-reservations-status" data-status-filter aria-controls="mx-reservations-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-reservations-size">Rijen per pagina</label>
                <select id="mx-reservations-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-reservations-from">Vanaf datum</label>
                <input id="mx-reservations-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-reservations-to">Tot en met datum</label>
                <input id="mx-reservations-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering reservations">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel reserveringen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Reserveringen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op verantwoordelijke" >
                                    Verantwoordelijke
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op vervalt op" >
                                    Vervalt op
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-reservations-body">
                        @forelse ($mxModules['reservations']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Verantwoordelijke" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Vervalt op" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van reservering {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen reserveringen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['reservations']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één reservering.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- INRUIL: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-tradeins"
        data-module="tradeins"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Voertuigen</span>
                <span class="mx-module-title">Inruil</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['tradeins']['connected'] ? $mxModules['tradeins']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="tradeins" aria-labelledby="mx-tradeins-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-tradeins-heading">Inruil</h2>
                    <p>Volg inruilaanvragen en de aangeleverde taxatiewaarde.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['tradeins']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['tradeins']['connected'] ? $mxModules['tradeins']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['tradeins']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['tradeins']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor inruil beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['tradeins']['total'] > $mxModules['tradeins']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-tradeins-search">Zoek in inruil</label>
                    <input id="mx-tradeins-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-tradeins-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-tradeins-status">Status of resultaat</label>
                    <select id="mx-tradeins-status" data-status-filter aria-controls="mx-tradeins-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-tradeins-size">Rijen per pagina</label>
                <select id="mx-tradeins-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-tradeins-from">Vanaf datum</label>
                <input id="mx-tradeins-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-tradeins-to">Tot en met datum</label>
                <input id="mx-tradeins-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering tradeins">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel inruil"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Inruil: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op eigenaar" >
                                    Eigenaar
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op inruilvoertuig" >
                                    Inruilvoertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="valuation" aria-label="Sorteer op taxatie" >
                                    Taxatie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op aangevraagd" >
                                    Aangevraagd
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-tradeins-body">
                        @forelse ($mxModules['tradeins']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Eigenaar" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Inruilvoertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="valuation" data-label="Taxatie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.valuation', $mxRow['valuation'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['valuation'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Aangevraagd" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van inruil {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen inruil geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['tradeins']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één inruil.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- FACTUREN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-invoices"
        data-module="invoices"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Financiën</span>
                <span class="mx-module-title">Facturen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['invoices']['connected'] ? $mxModules['invoices']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="invoices" aria-labelledby="mx-invoices-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-invoices-heading">Facturen</h2>
                    <p>Bekijk facturen en openstaande betaalstatussen.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['invoices']['rows']->count() }}</dd>
                </div>
                <div> <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['invoices']['connected'] ? $mxModules['invoices']['total'] : '—' }}</dd>
                </div>
                <div> <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['invoices']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['invoices']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor facturen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['invoices']['total'] > $mxModules['invoices']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-invoices-search">Zoek in facturen</label>
                    <input id="mx-invoices-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-invoices-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-invoices-status">Status of resultaat</label>
                    <select id="mx-invoices-status" data-status-filter aria-controls="mx-invoices-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-invoices-size">Rijen per pagina</label>
                <select id="mx-invoices-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-invoices-from">Vanaf datum</label>
                <input id="mx-invoices-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-invoices-to">Tot en met datum</label>
                <input id="mx-invoices-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering invoices">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel facturen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Facturen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op factuurnummer" >
                                    Factuurnummer
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="reference" aria-label="Sorteer op orderreferentie" >
                                    Orderreferentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="total" aria-label="Sorteer op bedrag" >
                                    Bedrag
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op vervaldatum" >
                                    Vervaldatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-invoices-body">
                        @forelse ($mxModules['invoices']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Factuurnummer" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="reference" data-label="Orderreferentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.reference', $mxRow['reference'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['reference'] ?? '') }}
                                </td>
                                <td data-field="total" data-label="Bedrag" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.total', $mxRow['total'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['total'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Vervaldatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van factuur {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen facturen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['invoices']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één factuur.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- BETALINGEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-payments"
        data-module="payments"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Financiën</span>
                <span class="mx-module-title">Betalingen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['payments']['connected'] ? $mxModules['payments']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="payments" aria-labelledby="mx-payments-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-payments-heading">Betalingen</h2>
                    <p>Bekijk aangeleverde betaalregistraties en afhandeling.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div> <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['payments']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['payments']['connected'] ? $mxModules['payments']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['payments']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['payments']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor betalingen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['payments']['total'] > $mxModules['payments']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-payments-search">Zoek in betalingen</label>
                    <input id="mx-payments-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-payments-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-payments-status">Status of resultaat</label>
                    <select id="mx-payments-status" data-status-filter aria-controls="mx-payments-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-payments-size">Rijen per pagina</label>
                <select id="mx-payments-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-payments-from">Vanaf datum</label>
                <input id="mx-payments-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-payments-to">Tot en met datum</label>
                <input id="mx-payments-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering payments">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel betalingen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Betalingen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op transactie" >
                                    Transactie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="method" aria-label="Sorteer op betaalmethode" >
                                    Betaalmethode
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="total" aria-label="Sorteer op bedrag" >
                                    Bedrag
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op betaaldatum" >
                                    Betaaldatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-payments-body">
                        @forelse ($mxModules['payments']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Transactie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="method" data-label="Betaalmethode" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.method', $mxRow['method'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['method'] ?? '') }}
                                </td>
                                <td data-field="total" data-label="Bedrag" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.total', $mxRow['total'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['total'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Betaaldatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van betaling {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen betalingen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['payments']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één betaling.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- TERUGBETALINGEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-refunds"
        data-module="refunds"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Financiën</span>
                <span class="mx-module-title">Terugbetalingen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['refunds']['connected'] ? $mxModules['refunds']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="refunds" aria-labelledby="mx-refunds-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-refunds-heading">Terugbetalingen</h2>
                    <p>Volg geregistreerde terugbetalingen.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['refunds']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['refunds']['connected'] ? $mxModules['refunds']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['refunds']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['refunds']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor terugbetalingen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['refunds']['total'] > $mxModules['refunds']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-refunds-search">Zoek in terugbetalingen</label>
                    <input id="mx-refunds-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-refunds-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-refunds-status">Status of resultaat</label>
                    <select id="mx-refunds-status" data-status-filter aria-controls="mx-refunds-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-refunds-size">Rijen per pagina</label>
                <select id="mx-refunds-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-refunds-from">Vanaf datum</label>
                <input id="mx-refunds-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-refunds-to">Tot en met datum</label>
                <input id="mx-refunds-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering refunds">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel terugbetalingen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Terugbetalingen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="reason" aria-label="Sorteer op reden" >
                                    Reden
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="total" aria-label="Sorteer op bedrag" >
                                    Bedrag
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op aangevraagd" >
                                    Aangevraagd
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-refunds-body">
                        @forelse ($mxModules['refunds']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="reason" data-label="Reden" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.reason', $mxRow['reason'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['reason'] ?? '') }}
                                </td>
                                <td data-field="total" data-label="Bedrag" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.total', $mxRow['total'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['total'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Aangevraagd" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van terugbetaling {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen terugbetalingen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['refunds']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één terugbetaling.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- AFLEVERINGEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-deliveries"
        data-module="deliveries"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Planning</span>
                <span class="mx-module-title">Afleveringen</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['deliveries']['connected'] ? $mxModules['deliveries']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="deliveries" aria-labelledby="mx-deliveries-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-deliveries-heading">Afleveringen</h2>
                    <p>Bekijk aflevermomenten en verantwoordelijke medewerkers.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['deliveries']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['deliveries']['connected'] ? $mxModules['deliveries']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['deliveries']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['deliveries']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor afleveringen beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['deliveries']['total'] > $mxModules['deliveries']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-deliveries-search">Zoek in afleveringen</label>
                    <input id="mx-deliveries-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-deliveries-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-deliveries-status">Status of resultaat</label>
                    <select id="mx-deliveries-status" data-status-filter aria-controls="mx-deliveries-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-deliveries-size">Rijen per pagina</label>
                <select id="mx-deliveries-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-deliveries-from">Vanaf datum</label>
                <input id="mx-deliveries-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-deliveries-to">Tot en met datum</label>
                <input id="mx-deliveries-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering deliveries">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel afleveringen"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Afleveringen: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op medewerker" >
                                    Medewerker
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op afleverdatum" >
                                    Afleverdatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-deliveries-body">
                        @forelse ($mxModules['deliveries']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Medewerker" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Afleverdatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van aflevering {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen afleveringen geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['deliveries']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één aflevering.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- WERKPLAATS: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-workshop"
        data-module="workshop"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Service</span>
                <span class="mx-module-title">Werkplaats</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['workshop']['connected'] ? $mxModules['workshop']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="workshop" aria-labelledby="mx-workshop-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-workshop-heading">Werkplaats</h2>
                    <p>Volg werkorders en de toegewezen werkplaats.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['workshop']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['workshop']['connected'] ? $mxModules['workshop']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['workshop']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['workshop']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor werkplaats beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['workshop']['total'] > $mxModules['workshop']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-workshop-search">Zoek in werkplaats</label>
                    <input id="mx-workshop-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-workshop-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-workshop-status">Status of resultaat</label>
                    <select id="mx-workshop-status" data-status-filter aria-controls="mx-workshop-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-workshop-size">Rijen per pagina</label>
                <select id="mx-workshop-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-workshop-from">Vanaf datum</label>
                <input id="mx-workshop-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-workshop-to">Tot en met datum</label>
                <input id="mx-workshop-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering workshop">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel werkplaats"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Werkplaats: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op werkordernummer" >
                                    Werkordernummer
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="subject" aria-label="Sorteer op werkzaamheden" >
                                    Werkzaamheden
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op monteur" >
                                    Monteur
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op gepland" >
                                    Gepland
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-workshop-body">
                        @forelse ($mxModules['workshop']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Werkordernummer" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Werkzaamheden" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Monteur" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Gepland" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van werkorder {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen werkplaats geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['workshop']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één werkorder.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- GARANTIES: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-warranties"
        data-module="warranties"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Service</span>
                <span class="mx-module-title">Garanties</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['warranties']['connected'] ? $mxModules['warranties']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="warranties" aria-labelledby="mx-warranties-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-warranties-heading">Garanties</h2>
                    <p>Bekijk garantiegevallen en hun behandeling.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['warranties']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['warranties']['connected'] ? $mxModules['warranties']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['warranties']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['warranties']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor garanties beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['warranties']['total'] > $mxModules['warranties']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-warranties-search">Zoek in garanties</label>
                    <input id="mx-warranties-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-warranties-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-warranties-status">Status of resultaat</label>
                    <select id="mx-warranties-status" data-status-filter aria-controls="mx-warranties-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-warranties-size">Rijen per pagina</label>
                <select id="mx-warranties-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-warranties-from">Vanaf datum</label>
                <input id="mx-warranties-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-warranties-to">Tot en met datum</label>
                <input id="mx-warranties-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering warranties">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel garanties"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Garanties: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="vehicle" aria-label="Sorteer op voertuig" >
                                    Voertuig
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="subject" aria-label="Sorteer op melding" >
                                    Melding
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op gemeld" >
                                    Gemeld
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-warranties-body">
                        @forelse ($mxModules['warranties']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="vehicle" data-label="Voertuig" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.vehicle', $mxRow['vehicle'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['vehicle'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Melding" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Gemeld" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van garantiegeval {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen garanties geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['warranties']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één garantiegeval.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- SUPPORT: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-tickets"
        data-module="tickets"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Service</span>
                <span class="mx-module-title">Support</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['tickets']['connected'] ? $mxModules['tickets']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="tickets" aria-labelledby="mx-tickets-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-tickets-heading">Support</h2>
                    <p>Bekijk klantvragen en de verantwoordelijke medewerker.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['tickets']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['tickets']['connected'] ? $mxModules['tickets']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['tickets']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['tickets']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor support beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['tickets']['total'] > $mxModules['tickets']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-tickets-search">Zoek in support</label>
                    <input id="mx-tickets-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-tickets-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-tickets-status">Status of resultaat</label>
                    <select id="mx-tickets-status" data-status-filter aria-controls="mx-tickets-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-tickets-size">Rijen per pagina</label>
                <select id="mx-tickets-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-tickets-from">Vanaf datum</label>
                <input id="mx-tickets-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-tickets-to">Tot en met datum</label>
                <input id="mx-tickets-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering tickets">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel support"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Support: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op ticketnummer" >
                                    Ticketnummer
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="customer" aria-label="Sorteer op klant" >
                                    Klant
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="subject" aria-label="Sorteer op onderwerp" >
                                    Onderwerp
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="owner" aria-label="Sorteer op medewerker" >
                                    Medewerker
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op aangemaakt" >
                                    Aangemaakt
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-tickets-body">
                        @forelse ($mxModules['tickets']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Ticketnummer" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="customer" data-label="Klant" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.customer', $mxRow['customer'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['customer'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Onderwerp" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Medewerker" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Aangemaakt" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van ticket {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen support geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['tickets']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één ticket.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- TAKEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-tasks"
        data-module="tasks"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Planning</span>
                <span class="mx-module-title">Taken</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['tasks']['connected'] ? $mxModules['tasks']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="tasks" aria-labelledby="mx-tasks-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-tasks-heading">Taken</h2>
                    <p>Houd toegewezen taken en deadlines in beeld.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['tasks']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['tasks']['connected'] ? $mxModules['tasks']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['tasks']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['tasks']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor taken beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['tasks']['total'] > $mxModules['tasks']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-tasks-search">Zoek in taken</label>
                    <input id="mx-tasks-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-tasks-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-tasks-status">Status of resultaat</label>
                    <select id="mx-tasks-status" data-status-filter aria-controls="mx-tasks-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-tasks-size">Rijen per pagina</label>
                <select id="mx-tasks-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-tasks-from">Vanaf datum</label>
                <input id="mx-tasks-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-tasks-to">Tot en met datum</label>
                <input id="mx-tasks-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering tasks">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel taken"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Taken: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="subject" aria-label="Sorteer op taak" >
                                    Taak
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="owner" aria-label="Sorteer op eigenaar" >
                                    Eigenaar
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="priority" aria-label="Sorteer op prioriteit" >
                                    Prioriteit
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op deadline" >
                                    Deadline
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-tasks-body">
                        @forelse ($mxModules['tasks']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Taak" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Eigenaar" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="priority" data-label="Prioriteit" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.priority', $mxRow['priority'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['priority'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Deadline" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van taak {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen taken geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['tasks']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één taak.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- CAMPAGNES: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-campaigns"
        data-module="campaigns"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Commercie</span>
                <span class="mx-module-title">Campagnes</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['campaigns']['connected'] ? $mxModules['campaigns']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="campaigns" aria-labelledby="mx-campaigns-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-campaigns-heading">Campagnes</h2>
                    <p>Bekijk marketingcampagnes en de aangeleverde resultaten.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['campaigns']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['campaigns']['connected'] ? $mxModules['campaigns']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['campaigns']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['campaigns']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor campagnes beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['campaigns']['total'] > $mxModules['campaigns']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-campaigns-search">Zoek in campagnes</label>
                    <input id="mx-campaigns-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-campaigns-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-campaigns-status">Status of resultaat</label>
                    <select id="mx-campaigns-status" data-status-filter aria-controls="mx-campaigns-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-campaigns-size">Rijen per pagina</label>
                <select id="mx-campaigns-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-campaigns-from">Vanaf datum</label>
                <input id="mx-campaigns-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-campaigns-to">Tot en met datum</label>
                <input id="mx-campaigns-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering campaigns">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel campagnes"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Campagnes: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="subject" aria-label="Sorteer op campagne" >
                                    Campagne
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="channel" aria-label="Sorteer op kanaal" >
                                    Kanaal
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="result" aria-label="Sorteer op resultaat" >
                                    Resultaat
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op startdatum" >
                                    Startdatum
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-campaigns-body">
                        @forelse ($mxModules['campaigns']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Campagne" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="channel" data-label="Kanaal" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.channel', $mxRow['channel'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['channel'] ?? '') }}
                                </td>
                                <td data-field="result" data-label="Resultaat" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.result', $mxRow['result'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['result'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Startdatum" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van campagne {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen campagnes geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['campaigns']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één campagne.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- IMPORTS: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-imports"
        data-module="imports"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Beheer</span>
                <span class="mx-module-title">Imports</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['imports']['connected'] ? $mxModules['imports']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="imports" aria-labelledby="mx-imports-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-imports-heading">Imports</h2>
                    <p>Controleer importtaken en hun verwerkingsresultaat.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['imports']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['imports']['connected'] ? $mxModules['imports']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['imports']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['imports']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor imports beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['imports']['total'] > $mxModules['imports']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-imports-search">Zoek in imports</label>
                    <input id="mx-imports-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-imports-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-imports-status">Status of resultaat</label>
                    <select id="mx-imports-status" data-status-filter aria-controls="mx-imports-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-imports-size">Rijen per pagina</label>
                <select id="mx-imports-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-imports-from">Vanaf datum</label>
                <input id="mx-imports-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-imports-to">Tot en met datum</label>
                <input id="mx-imports-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering imports">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel imports"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Imports: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="subject" aria-label="Sorteer op bronbestand" >
                                    Bronbestand
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="owner" aria-label="Sorteer op gestart door" >
                                    Gestart door
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="result" aria-label="Sorteer op resultaat" >
                                    Resultaat
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op gestart" >
                                    Gestart
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-imports-body">
                        @forelse ($mxModules['imports']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Bronbestand" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Gestart door" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="result" data-label="Resultaat" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.result', $mxRow['result'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['result'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Gestart" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van import {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen imports geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['imports']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één import.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- EXPORTS: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-exports"
        data-module="exports"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Beheer</span>
                <span class="mx-module-title">Exports</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['exports']['connected'] ? $mxModules['exports']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="exports" aria-labelledby="mx-exports-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-exports-heading">Exports</h2>
                    <p>Bekijk serverexports die door de controller zijn aangeleverd.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['exports']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['exports']['connected'] ? $mxModules['exports']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['exports']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['exports']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor exports beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['exports']['total'] > $mxModules['exports']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-exports-search">Zoek in exports</label>
                    <input id="mx-exports-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-exports-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-exports-status">Status of resultaat</label>
                    <select id="mx-exports-status" data-status-filter aria-controls="mx-exports-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-exports-size">Rijen per pagina</label>
                <select id="mx-exports-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-exports-from">Vanaf datum</label>
                <input id="mx-exports-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-exports-to">Tot en met datum</label>
                <input id="mx-exports-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering exports">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel exports"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Exports: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="subject" aria-label="Sorteer op rapport" >
                                    Rapport
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="owner" aria-label="Sorteer op aangevraagd door" >
                                    Aangevraagd door
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="result" aria-label="Sorteer op resultaat" >
                                    Resultaat
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op status" >
                                    Status
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op aangevraagd" >
                                    Aangevraagd
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-exports-body">
                        @forelse ($mxModules['exports']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Rapport" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Aangevraagd door" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="result" data-label="Resultaat" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.result', $mxRow['result'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['result'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Status" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Aangevraagd" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van export {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen exports geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['exports']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één export.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>

    {-- ACTIVITEITEN: alleen-lezen rapport met eigen filters en paginering. --}
    <details
        class="md-panel mx-module"
        id="mx-module-audit"
        data-module="audit"
    >
        <summary class="mx-module-summary">
            <span class="mx-module-heading">
                <span class="md-section-kicker">Beheer</span>
                <span class="mx-module-title">Activiteiten</span>
            </span>
            <span class="mx-module-state">
                {{ $mxModules['audit']['connected'] ? $mxModules['audit']['rows']->count().' geladen' : 'Niet aangesloten' }}
            </span>
        </summary>
        <div class="mx-module-body" data-grid="audit" aria-labelledby="mx-audit-heading">
            <header class="md-section-head">
                <div>
                    <h2 id="mx-audit-heading">Activiteiten</h2>
                    <p>Bekijk door de server aangeleverde auditgebeurtenissen.</p>
                </div>
                <span class="mx-scope">Alleen geladen selectie</span>
            </header>
            <dl class="mx-module-meta">
                <div>
                    <dt>Geladen rijen</dt>
                    <dd>{{ $mxModules['audit']['rows']->count() }}</dd>
                </div>
                <div>
                    <dt>Aangeleverd totaal</dt>
                    <dd>{{ $mxModules['audit']['connected'] ? $mxModules['audit']['total'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Gegevens bijgewerkt</dt>
                    <dd>{{ $mxModules['audit']['updated_at'] ?: 'Niet opgegeven' }}</dd>
                </div>
            </dl>
            @if (! $mxModules['audit']['connected'])
                <div class="mx-alert" role="note">
                    <strong>Dit overzicht is nog niet aangesloten.</strong>
                    <p>Er zijn nog geen gegevens voor activiteiten beschikbaar in dit dashboard.</p>
                </div>
            @elseif ($mxModules['audit']['total'] > $mxModules['audit']['rows']->count())
                <div class="mx-alert" role="note">
                    <strong>Je bekijkt een deel van het totaal.</strong>
                    <p>Zoeken, sorteren en downloaden gebruiken uitsluitend de geladen rijen.</p>
                </div>
            @endif
            <div class="mx-tools">
                <div class="mx-field mx-grow">
                    <label for="mx-audit-search">Zoek in activiteiten</label>
                    <input id="mx-audit-search" type="search" data-grid-search autocomplete="off" placeholder="Zoek op de zichtbare velden…" aria-controls="mx-audit-body" >
                </div>
                <div class="mx-field">
                    <label for="mx-audit-status">Status of resultaat</label>
                    <select id="mx-audit-status" data-status-filter aria-controls="mx-audit-body" >
                        <option value="">Alle statussen</option>
                    </select>
                </div>
            </div>
        <div class="mx-tools" data-grid-tools>
            <div class="mx-field">
                <label for="mx-audit-size">Rijen per pagina</label>
                <select id="mx-audit-size" data-page-size>
                    <option value="10">10 rijen</option>
                    <option value="25" selected>25 rijen</option>
                    <option value="50">50 rijen</option>
                    <option value="100">100 rijen</option>
                </select>
            </div>
            <div class="mx-field">
                <label for="mx-audit-from">Vanaf datum</label>
                <input id="mx-audit-from" type="date" data-date-from>
            </div>
            <div class="mx-field">
                <label for="mx-audit-to">Tot en met datum</label>
                <input id="mx-audit-to" type="date" data-date-to>
            </div>
            <div class="mx-tool-actions">
                <button class="md-btn secondary" type="button" data-reset>
                    Filters wissen
                </button>
                <button class="md-btn secondary" type="button" data-export="csv">
                    CSV downloaden
                </button>
                <button class="md-btn secondary" type="button" data-export="json">
                    JSON downloaden
                </button>
            </div>
        </div>
        <p class="mx-grid-notice" data-grid-notice role="status"></p>
        <nav class="mx-pagination" aria-label="Paginering audit">
            <button type="button" class="md-btn secondary" data-page="first" aria-label="Eerste pagina">«</button>
            <button type="button" class="md-btn secondary" data-page="previous">Vorige</button>
            <span class="mx-page-label" data-page-label aria-live="polite"></span>
            <button type="button" class="md-btn secondary" data-page="next">Volgende</button>
            <button type="button" class="md-btn secondary" data-page="last" aria-label="Laatste pagina">»</button>
        </nav>
            <div
                class="md-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Tabel activiteiten"
            >
                <table class="md-table mx-report-table">
                    <caption class="mx-sr-only">
                        Activiteiten: geladen gegevens, met sorteerknoppen in de kolomkoppen.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="0" data-sort-key="number" aria-label="Sorteer op referentie" >
                                    Referentie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="1" data-sort-key="owner" aria-label="Sorteer op uitgevoerd door" >
                                    Uitgevoerd door
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="2" data-sort-key="subject" aria-label="Sorteer op actie" >
                                    Actie
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="3" data-sort-key="reference" aria-label="Sorteer op object" >
                                    Object
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="4" data-sort-key="status" aria-label="Sorteer op resultaat" >
                                    Resultaat
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col" aria-sort="none">
                                <button type="button" data-sort="5" data-sort-key="date" aria-label="Sorteer op tijdstip" >
                                    Tijdstip
                                    <span aria-hidden="true">↕</span> </button>
                            </th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody id="mx-audit-body">
                        @forelse ($mxModules['audit']['rows'] as $mxRow)
                            <tr
                                data-grid-row
                                data-status="{{ $mxText($mxRow['status'] ?? '') }}"
                                data-created="{{ substr($mxText($mxRow['date'] ?? ''), 0, 10) }}"
                            >
                                <td data-field="number" data-label="Referentie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.number', $mxRow['number'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['number'] ?? '') }}
                                </td>
                                <td data-field="owner" data-label="Uitgevoerd door" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.owner', $mxRow['owner'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['owner'] ?? '') }}
                                </td>
                                <td data-field="subject" data-label="Actie" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.subject', $mxRow['subject'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['subject'] ?? '') }}
                                </td>
                                <td data-field="reference" data-label="Object" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.reference', $mxRow['reference'] ?? '')) }}" >
                                    {{ $mxDisplay($mxRow['reference'] ?? '') }}
                                </td>
                                <td data-field="status" data-label="Resultaat" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.status', $mxRow['status'] ?? '')) }}" >
                                    <span class="md-badge mx-status-badge">
                                        {{ $mxText($mxRow['status'] ?? '') ?: 'Niet opgegeven' }}
                                    </span>
                                </td>
                                <td data-field="date" data-label="Tijdstip" data-sort-value="{{ $mxText(data_get($mxRow, 'sort.date', $mxRow['date'] ?? '')) }}" >
                                    <span class="mx-date">
                                        {{ $mxDisplay($mxRow['date'] ?? '') }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="md-btn secondary" data-detail aria-label="Details van activiteit {{ $mxText($mxRow['number'] ?? '') }}" >
                                        Bekijken
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr class="mx-server-empty">
                                <td colspan="7">
                                    <div class="md-empty">
                                        <div class="md-empty-mark" aria-hidden="true">—</div>
                                        <h3>Geen activiteiten geladen</h3>
                                        <p>Nieuwe gegevens verschijnen hier nadat het overzicht door de server is gevuld.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-empty" data-grid-empty role="status" hidden>
                <strong>Geen overeenkomsten</strong>
                <p>Verruim de zoekopdracht, kies een andere status of wis de filters.</p>
            </div>
            <footer class="md-table-footer">
                <span data-grid-summary role="status">
                    {{ $mxModules['audit']['rows']->count() }} rijen geladen
                </span>
                <span class="mx-muted">Downloads bevatten alle gefilterde rijen.</span>
            </footer>
            <details class="mx-help">
                <summary>Uitleg bij dit overzicht</summary>
                <p>Combineer zoeken, status en datumbereik. Meerdere zoekwoorden moeten allemaal voorkomen.</p>
                <p>Klik op een kolomkop om oplopend of aflopend te sorteren.</p>
                <p>De detailweergave toont de aangeleverde velden van één activiteit.</p>
                <p>Dit rapport wijzigt geen gegevens. Gebruik de bestaande beheerpagina voor mutaties.</p>
            </details>
        </div>
    </details>
    <dialog id="mx-detail-dialog" class="mx-dialog" aria-labelledby="mx-detail-title">
        <h2 id="mx-detail-title" data-detail-title>Details</h2>
        <p>De velden hieronder komen uit de geladen selectie.</p>
        <dl class="mx-detail-list" data-detail-content></dl>
        <div class="mx-dialog-actions">
            <button type="button" class="md-btn secondary" data-detail-copy>Kopiëren</button>
            <button type="button" class="md-btn" data-dialog-close autofocus>Sluiten</button>
        </div>
    </dialog>
    <dialog id="mx-command-dialog" class="mx-dialog" aria-labelledby="mx-command-title">
        <h2 id="mx-command-title">Snel naar een werkgebied</h2>
        <div class="mx-field">
            <label for="mx-command-search">Bestemming zoeken</label>
            <input id="mx-command-search" type="search" data-command-search autofocus autocomplete="off">
        </div>
        <p id="mx-command-help">Gebruik de pijltjestoetsen en Enter om een bestemming te openen. Escape sluit dit venster.</p>
        <div class="mx-command-results" data-command-results aria-describedby="mx-command-help"></div>
        <p data-command-count role="status"></p>
        <div class="mx-dialog-actions">
            <button type="button" class="md-btn secondary" data-dialog-close>Sluiten</button>
        </div>
    </dialog>
    <dialog id="mx-delete-dialog" class="mx-dialog" aria-labelledby="mx-delete-title" aria-describedby="mx-delete-description">
        <h2 id="mx-delete-title">Account verwijderen?</h2>
        <p id="mx-delete-description">
            Je staat op het punt het account van <strong data-delete-name></strong> te verwijderen.
            Deze actie gebruikt de bestaande verwijderroute van je applicatie.
        </p>
        <div class="mx-field">
            <label for="mx-delete-input">Typ VERWIJDER om te bevestigen</label>
            <input id="mx-delete-input" type="text" data-delete-input autocomplete="off" spellcheck="false" autofocus>
        </div>
        <p>Controleer zorgvuldig of je het juiste account hebt geselecteerd.</p>
        <div class="mx-dialog-actions">
            <button type="button" class="md-btn secondary" data-dialog-close>Annuleren</button>
            <button type="button" class="md-btn danger" data-delete-confirm disabled>Definitief verwijderen</button>
        </div>
    </dialog>
    <div class="mx-toast-region" data-toast-region role="status" aria-live="polite" aria-atomic="true"></div>

</div>
<script nonce="{{ $cspNonce ?? '' }}">
(() => {
    'use strict';

    const start = () => {
        const root = document.getElementById('mashalDashboard');
        if (!root || root.dataset.initialized === 'true') return;
        root.dataset.initialized = 'true';

        const query = (selector, scope = root) => scope.querySelector(selector);
        const queryAll = (selector, scope = root) => Array.from(scope.querySelectorAll(selector));
        const normalize = value => String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('nl-NL')
            .trim();
        const collator = new Intl.Collator('nl-NL', { numeric: true, sensitivity: 'base' });
        const number = value => new Intl.NumberFormat('nl-NL').format(value);
        const debounce = (callback, delay = 160) => {
            let timer;
            const wrapped = (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => callback(...args), delay);
            };
            wrapped.cancel = () => clearTimeout(timer);
            return wrapped;
        };
        const create = (tag, text, className) => {
            const node = document.createElement(tag);
            if (text !== undefined) node.textContent = text;
            if (className) node.className = className;
            return node;
        };
        const preferenceKey = 'mashal.dashboard.expert.preferences.v1';
        const preferences = {
            theme: 'dark',
            density: 'comfortable',
            connectedOnly: false,
        };
        try {
            const saved = JSON.parse(localStorage.getItem(preferenceKey) || '{}');
            if (saved && ['dark', 'light'].includes(saved.theme)) preferences.theme = saved.theme;
            if (saved && ['comfortable', 'compact'].includes(saved.density)) preferences.density = saved.density;
            if (saved && typeof saved.connectedOnly === 'boolean') preferences.connectedOnly = saved.connectedOnly;
        } catch (_) {
            // Private browsing and disabled storage must not break the dashboard.
        }
        const savePreferences = () => {
            try {
                localStorage.setItem(preferenceKey, JSON.stringify(preferences));
            } catch (_) {
                // Settings remain active for the current page without persistence.
            }
        };
        const applyPreferences = () => {
            root.dataset.theme = preferences.theme;
            root.dataset.density = preferences.density;
            const theme = query('[data-theme-toggle]');
            const density = query('[data-density-toggle]');
            theme.setAttribute('aria-pressed', String(preferences.theme === 'light'));
            theme.textContent = preferences.theme === 'light' ? 'Donker thema' : 'Licht thema';
            density.setAttribute('aria-pressed', String(preferences.density === 'compact'));
            density.textContent = preferences.density === 'compact' ? 'Ruime weergave' : 'Compacte weergave';
        };
        query('[data-theme-toggle]').addEventListener('click', () => {
            preferences.theme = preferences.theme === 'dark' ? 'light' : 'dark';
            applyPreferences();
            savePreferences();
        });
        query('[data-density-toggle]').addEventListener('click', () => {
            preferences.density = preferences.density === 'comfortable' ? 'compact' : 'comfortable';
            applyPreferences();
            savePreferences();
        });
        applyPreferences();

        const toastRegion = query('[data-toast-region]');
        const toast = (message, error = false) => {
            const item = create('div', message, error ? 'mx-toast mx-toast-error' : 'mx-toast');
            toastRegion.replaceChildren(item);
            setTimeout(() => item.remove(), 6000);
        };
        const setConnection = () => {
            const notice = query('[data-connection]');
            notice.hidden = navigator.onLine;
            notice.textContent = navigator.onLine ? '' : 'Je browser meldt dat je offline bent. Geladen gegevens blijven doorzoekbaar; serveracties kunnen mislukken.';
        };
        window.addEventListener('online', setConnection);
        window.addEventListener('offline', setConnection);
        setConnection();

        let returnFocus = null;
        const openDialog = dialog => {
            if (!dialog) return;
            returnFocus = document.activeElement;
            if (typeof dialog.showModal === 'function') dialog.showModal();
            else {
                toast('Je browser ondersteunt dit venster niet. Gebruik een recente browser.', true);
                return;
            }
            dialog.querySelector('[autofocus], input, button')?.focus();
        };
        queryAll('dialog').forEach(dialog => {
            dialog.addEventListener('close', () => {
                if (returnFocus?.isConnected) returnFocus.focus();
            });
            queryAll('[data-dialog-close]', dialog).forEach(button => {
                button.addEventListener('click', () => dialog.close());
            });
            dialog.addEventListener('click', event => {
                if (event.target !== dialog) return;
                const rectangle = dialog.getBoundingClientRect();
                const inside = event.clientX >= rectangle.left && event.clientX <= rectangle.right
                    && event.clientY >= rectangle.top && event.clientY <= rectangle.bottom;
                if (!inside) dialog.close();
            });
        });

        const csvCell = value => {
            let text = String(value ?? '').replace(/\u0000/g, '');
            // Leading whitespace/control characters can hide a formula prefix.
            if (/^[\s\u0000-\u001f]*[=+\-@]/.test(text) || /^[\t\r\n]/.test(text)) text = "'" + text;
            return '"' + text.replace(/"/g, '""') + '"';
        };
        const download = (contents, type, filename) => {
            const blob = new Blob([contents], { type });
            const url = URL.createObjectURL(blob);
            const link = create('a');
            link.href = url;
            link.download = filename;
            document.body.append(link);
            link.click();
            link.remove();
            setTimeout(() => URL.revokeObjectURL(url), 1500);
        };
        const isoDay = value => {
            const day = String(value || '').slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(day)) return '';
            const date = new Date(day + 'T00:00:00Z');
            return Number.isNaN(date.getTime()) || date.toISOString().slice(0, 10) !== day ? '' : day;
        };

        class DataGrid {
            constructor(element) {
                this.element = element;
                this.id = element.dataset.grid;
                this.body = query('tbody', element);
                this.rows = queryAll('[data-grid-row]', element);
                this.search = query('[data-grid-search]', element);
                this.status = query('[data-status-filter]', element);
                this.from = query('[data-date-from]', element);
                this.to = query('[data-date-to]', element);
                this.sizeControl = query('[data-page-size]', element);
                this.headers = queryAll('thead th', element).slice(0, -1);
                this.page = 1;
                this.size = Number(this.sizeControl?.value || 25);
                this.sortColumn = null;
                this.sortDirection = 1;
                this.filter = 'all';
                this.filtered = [];
                this.records = new Map();
                this.rows.forEach((row, index) => {
                    const cells = Array.from(row.cells).slice(0, -1);
                    this.records.set(row, {
                        index,
                        values: cells.map(cell => cell.textContent.trim().replace(/\s+/g, ' ')),
                        sortable: cells.map(cell => cell.dataset.sortValue ?? cell.textContent.trim()),
                        search: normalize(row.textContent + ' ' + (row.dataset.provider || '')),
                        day: isoDay(row.dataset.created),
                    });
                });
                this.bind();
                this.apply();
            }

            bind() {
                this.debouncedApply = debounce(() => {
                    this.page = 1;
                    this.apply();
                });
                this.search?.addEventListener('input', this.debouncedApply);
                [this.status, this.from, this.to].filter(Boolean).forEach(control => {
                    control.addEventListener('change', () => {
                        this.page = 1;
                        this.apply();
                    });
                });
                if (this.status) {
                    const statuses = Array.from(new Set(this.rows.map(row => row.dataset.status || '').filter(Boolean)));
                    statuses.sort(collator.compare).forEach(status => {
                        const option = create('option', status);
                        option.value = status;
                        this.status.append(option);
                    });
                }
                this.sizeControl?.addEventListener('change', () => {
                    this.size = [10, 25, 50, 100].includes(Number(this.sizeControl.value))
                        ? Number(this.sizeControl.value) : 25;
                    this.page = 1;
                    this.apply();
                });
                queryAll('[data-filter]', this.element).forEach(button => {
                    button.setAttribute('aria-pressed', String(button.dataset.filter === 'all'));
                    button.addEventListener('click', () => {
                        this.filter = button.dataset.filter;
                        this.page = 1;
                        this.apply();
                    });
                });
                queryAll('[data-sort]', this.element).forEach(button => {
                    button.addEventListener('click', () => {
                        const column = Number(button.dataset.sort);
                        this.sortDirection = this.sortColumn === column ? -this.sortDirection : 1;
                        this.sortColumn = column;
                        this.page = 1;
                        this.apply();
                    });
                });
                queryAll('[data-page]', this.element).forEach(button => {
                    button.addEventListener('click', () => {
                        const totalPages = Math.max(1, Math.ceil(this.filtered.length / this.size));
                        const action = button.dataset.page;
                        if (action === 'first') this.page = 1;
                        if (action === 'previous') this.page--;
                        if (action === 'next') this.page++;
                        if (action === 'last') this.page = totalPages;
                        this.apply();
                    });
                });
                query('[data-reset]', this.element)?.addEventListener('click', () => this.reset());
                queryAll('[data-export]', this.element).forEach(button => {
                    button.addEventListener('click', () => this.export(button.dataset.export));
                });
                this.element.addEventListener('click', event => {
                    const button = event.target.closest('[data-detail]');
                    if (button) this.detail(button.closest('[data-grid-row]'));
                });
            }

            matchesUserFilter(row) {
                const data = row.dataset;
                switch (this.filter) {
                    case 'verified': return data.verified === '1';
                    case 'pending': return data.verified !== '1';
                    case 'admin': return data.admin === '1';
                    case 'photo': return data.photo === '1';
                    case 'social_avatar': return data.socialAvatar === '1';
                    case 'all': return true;
                    default: return data.provider === this.filter;
                }
            }

            apply() {
                const terms = normalize(this.search?.value || '').split(/\s+/).filter(Boolean);
                const from = isoDay(this.from?.value);
                const to = isoDay(this.to?.value);
                const invalidRange = Boolean(from && to && from > to);
                const notice = query('[data-grid-notice]', this.element);
                notice.textContent = invalidRange ? 'De begindatum moet vóór of op de einddatum liggen.' : '';
                this.from?.setAttribute('aria-invalid', String(invalidRange));
                this.to?.setAttribute('aria-invalid', String(invalidRange));
                this.filtered = this.rows.filter(row => {
                    const record = this.records.get(row);
                    if (invalidRange) return false;
                    if (!terms.every(term => record.search.includes(term))) return false;
                    if (this.status?.value && row.dataset.status !== this.status.value) return false;
                    if (from && (!record.day || record.day < from)) return false;
                    if (to && (!record.day || record.day > to)) return false;
                    return this.id !== 'users' || this.matchesUserFilter(row);
                });
                if (this.sortColumn !== null) {
                    const column = this.sortColumn;
                    this.filtered.sort((left, right) => {
                        const a = this.records.get(left);
                        const b = this.records.get(right);
                        let value;
                        if (this.id === 'users' && column === 5) value = collator.compare(a.day, b.day);
                        else {
                            const x = a.sortable[column] || '';
                            const y = b.sortable[column] || '';
                            const numeric = /^-?\d+(\.\d+)?$/;
                            value = numeric.test(x) && numeric.test(y)
                                ? Number(x) - Number(y)
                                : collator.compare(x, y);
                        }
                        return value * this.sortDirection || a.index - b.index;
                    });
                }
                const pages = Math.max(1, Math.ceil(this.filtered.length / this.size));
                this.page = Math.max(1, Math.min(pages, this.page));
                const start = (this.page - 1) * this.size;
                const visible = this.filtered.slice(start, start + this.size);
                this.rows.forEach(row => { row.hidden = true; });
                const fragment = document.createDocumentFragment();
                const visibleSet = new Set(visible);
                this.filtered.forEach(row => {
                    row.hidden = !visibleSet.has(row);
                    fragment.append(row);
                });
                this.body.append(fragment);
                const summary = query('[data-grid-summary]', this.element);
                if (summary) {
                    const first = this.filtered.length ? start + 1 : 0;
                    const last = Math.min(start + this.size, this.filtered.length);
                    summary.textContent = `${number(first)}–${number(last)} van ${number(this.filtered.length)} resultaten • ${number(this.rows.length)} geladen`;
                }
                const empty = query('[data-grid-empty]', this.element);
                if (empty) {
                    const show = this.rows.length > 0 && this.filtered.length === 0;
                    empty.hidden = !show;
                    empty.style.display = show ? 'block' : 'none';
                }
                query('[data-page-label]', this.element).textContent = `Pagina ${this.page} van ${pages}`;
                queryAll('[data-page]', this.element).forEach(button => {
                    const backward = ['first', 'previous'].includes(button.dataset.page);
                    button.disabled = backward ? this.page <= 1 : this.page >= pages;
                });
                queryAll('[data-export]', this.element).forEach(button => {
                    button.disabled = this.filtered.length === 0;
                });
                this.headers.forEach((header, index) => {
                    header.setAttribute('aria-sort', index !== this.sortColumn ? 'none'
                        : this.sortDirection === 1 ? 'ascending' : 'descending');
                });
                queryAll('[data-filter]', this.element).forEach(button => {
                    const active = button.dataset.filter === this.filter;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', String(active));
                });
            }

            reset() {
                this.debouncedApply.cancel();
                [this.search, this.status, this.from, this.to].filter(Boolean).forEach(control => { control.value = ''; });
                this.page = 1;
                this.filter = 'all';
                this.sortColumn = null;
                this.sortDirection = 1;
                this.apply();
                this.search?.focus();
            }

            labels() {
                return this.headers.map(header => header.textContent.replace(/↕/g, '').trim());
            }

            export(format) {
                // Flush a pending search before taking the export snapshot.
                this.debouncedApply.cancel();
                this.apply();
                if (!this.filtered.length) {
                    toast('Er zijn geen rijen om te downloaden.');
                    return;
                }
                const labels = this.labels();
                const rows = this.filtered.map(row => this.records.get(row).values);
                const stamp = new Date().toISOString().slice(0, 10);
                const filename = `mashal-${this.id}-${stamp}.${format}`;
                try {
                    if (format === 'json') {
                        const payload = {
                            report: this.id,
                            generatedAt: new Date().toISOString(),
                            scope: 'filtered_loaded_rows',
                            loadedRows: this.rows.length,
                            exportedRows: rows.length,
                            columns: labels,
                            rows,
                        };
                        download(JSON.stringify(payload, null, 2), 'application/json;charset=utf-8', filename);
                    } else {
                        const lines = [labels, ...rows].map(values => values.map(csvCell).join(';'));
                        download('\uFEFF' + lines.join('\r\n'), 'text/csv;charset=utf-8', filename);
                    }
                    toast(`${number(rows.length)} rijen aangeboden als ${format.toUpperCase()}-download.`);
                } catch (_) {
                    toast('De download kon niet worden gestart. Probeer een kleinere geladen selectie.', true);
                }
            }

            detail(row) {
                if (!row || !this.records.has(row)) return;
                const dialog = query('#mx-detail-dialog');
                const container = query('[data-detail-content]', dialog);
                const values = this.records.get(row).values;
                container.replaceChildren();
                this.labels().forEach((label, index) => {
                    const wrapper = create('div');
                    wrapper.append(create('dt', label), create('dd', values[index] || '—'));
                    container.append(wrapper);
                });
                query('[data-detail-title]', dialog).textContent = this.id === 'users' ? 'Accountdetails' : 'Rapportdetails';
                query('[data-detail-copy]', dialog).onclick = async () => {
                    const content = this.labels().map((label, index) => `${label}: ${values[index]}`).join('\n');
                    try {
                        if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
                        await navigator.clipboard.writeText(content);
                        toast('Details gekopieerd.');
                    } catch (_) {
                        toast('Kopiëren is niet beschikbaar. Selecteer de tekst in het venster.', true);
                    }
                };
                openDialog(dialog);
            }
        }

        const grids = queryAll('[data-grid]').map(element => new DataGrid(element));
        const userGrid = grids.find(grid => grid.id === 'users');

        const renderBars = (target, entries) => {
            const container = query(`[data-chart="${target}"]`);
            if (!container) return;
            container.replaceChildren();
            const sum = entries.reduce((total, [, count]) => total + count, 0);
            const max = Math.max(1, ...entries.map(([, count]) => count));
            if (!sum) {
                container.append(create('p', 'Geen geladen gegevens beschikbaar.', 'mx-muted'));
                return;
            }
            const list = create('ul', undefined, 'mx-bars');
            entries.forEach(([label, count]) => {
                const item = create('li', undefined, 'mx-bar-row');
                const header = create('div', undefined, 'mx-bar-label');
                header.append(create('span', label), create('strong', number(count)));
                const track = create('div', undefined, 'mx-bar-track');
                track.setAttribute('aria-hidden', 'true');
                const fill = create('span', undefined, 'mx-bar-fill');
                fill.style.width = `${Math.max(0, Math.min(100, count / max * 100))}%`;
                track.append(fill);
                item.append(header, track);
                list.append(item);
            });
            container.append(list);
        };
        if (userGrid) {
            const rows = userGrid.rows;
            const count = predicate => rows.filter(predicate).length;
            const verified = count(row => row.dataset.verified === '1');
            const admins = count(row => row.dataset.admin === '1');
            renderBars('verification', [['Geverifieerd', verified], ['Niet geverifieerd', rows.length - verified]]);
            renderBars('roles', [['Beheerder', admins], ['Gebruiker', rows.length - admins]]);
            const providers = new Map();
            const months = new Map();
            rows.forEach(row => {
                const provider = row.dataset.provider || 'Onbekend';
                providers.set(provider, (providers.get(provider) || 0) + 1);
                const day = isoDay(row.dataset.created);
                if (day) {
                    const month = day.slice(0, 7);
                    months.set(month, (months.get(month) || 0) + 1);
                }
            });
            renderBars('providers', [...providers].sort((a, b) => b[1] - a[1]));
            renderBars('registrations', [...months].sort((a, b) => collator.compare(a[0], b[0])).slice(-12));
        }

        const moduleSearch = query('#mx-module-search');
        const moduleGroup = query('#mx-module-group');
        const connectedOnly = query('#mx-connected-only');
        const moduleCards = queryAll('[data-module-link]');
        connectedOnly.checked = preferences.connectedOnly;
        const filterModules = () => {
            const text = normalize(moduleSearch.value);
            let visible = 0;
            moduleCards.forEach(card => {
                const matches = normalize(card.textContent).includes(text)
                    && (!moduleGroup.value || card.dataset.moduleGroup === moduleGroup.value)
                    && (!connectedOnly.checked || card.dataset.moduleConnected === '1');
                card.hidden = !matches;
                if (matches) visible++;
            });
            query('[data-module-empty]').hidden = visible > 0;
        };
        moduleSearch.addEventListener('input', filterModules);
        moduleGroup.addEventListener('change', filterModules);
        connectedOnly.addEventListener('change', () => {
            preferences.connectedOnly = connectedOnly.checked;
            savePreferences();
            filterModules();
        });
        filterModules();
        const navigateTo = id => {
            const destination = document.getElementById(id);
            if (!destination || !root.contains(destination)) return;
            if (destination.tagName === 'DETAILS') destination.open = true;
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            destination.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
            const focusable = destination.querySelector('summary, input, button');
            focusable?.focus({ preventScroll: true });
        };
        moduleCards.forEach(card => {
            card.addEventListener('click', event => {
                event.preventDefault();
                const id = card.getAttribute('href').slice(1);
                navigateTo(id);
                history.replaceState(null, '', '#' + id);
            });
        });
        const routeHash = () => {
            const id = location.hash.slice(1);
            if (id.startsWith('mx-module-')) navigateTo(id);
        };
        window.addEventListener('hashchange', routeHash);
        routeHash();

        const commandDialog = query('#mx-command-dialog');
        const commandSearch = query('[data-command-search]', commandDialog);
        const commandResults = query('[data-command-results]', commandDialog);
        const commands = [
            { title: 'Gebruikersoverzicht', group: 'Accounts', id: 'mx-users' },
            ...moduleCards.map(card => ({
                title: card.dataset.moduleTitle,
                group: card.dataset.moduleGroup,
                id: card.getAttribute('href').slice(1),
            })),
        ];
        const renderCommands = () => {
            commandResults.replaceChildren();
            const text = normalize(commandSearch.value);
            const matching = commands.filter(item => normalize(item.title + ' ' + item.group).includes(text));
            matching.forEach(item => {
                const button = create('button', undefined, 'mx-command-item');
                button.type = 'button';
                button.append(create('strong', item.title), create('span', item.group));
                button.addEventListener('click', () => {
                    commandDialog.close();
                    navigateTo(item.id);
                });
                commandResults.append(button);
            });
            if (!matching.length) commandResults.append(create('p', 'Geen overeenkomsten.'));
            query('[data-command-count]', commandDialog).textContent = `${matching.length} bestemmingen`;
        };
        const openCommand = () => {
            commandSearch.value = '';
            renderCommands();
            openDialog(commandDialog);
            commandSearch.focus();
        };
        query('[data-open-command]').addEventListener('click', openCommand);
        commandSearch.addEventListener('input', renderCommands);
        commandDialog.addEventListener('keydown', event => {
            const buttons = queryAll('.mx-command-item', commandDialog);
            const index = buttons.indexOf(document.activeElement);
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                buttons[Math.min(index + 1, buttons.length - 1)]?.focus();
            }
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (index <= 0) commandSearch.focus();
                else buttons[index - 1].focus();
            }
            if (event.key === 'Enter' && document.activeElement === commandSearch) {
                event.preventDefault();
                buttons[0]?.click();
            }
        });
        document.addEventListener('keydown', event => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                if (queryAll('dialog[open]').length) return;
                event.preventDefault();
                openCommand();
            }
        });

        const deleteDialog = query('#mx-delete-dialog');
        const deleteInput = query('[data-delete-input]', deleteDialog);
        const deleteButton = query('[data-delete-confirm]', deleteDialog);
        let pendingForm = null;
        let authorizedForm = null;
        queryAll('form[data-confirm-delete]').forEach(form => {
            query('button[type="submit"]', form).disabled = false;
            form.addEventListener('submit', event => {
                if (authorizedForm === form) {
                    authorizedForm = null;
                    const button = query('button[type="submit"]', form);
                    button.disabled = true;
                    button.textContent = 'Bezig…';
                    return;
                }
                event.preventDefault();
                pendingForm = form;
                query('[data-delete-name]', deleteDialog).textContent = form.dataset.confirmDelete;
                deleteInput.value = '';
                deleteButton.disabled = true;
                openDialog(deleteDialog);
            });
        });
        deleteInput.addEventListener('input', () => {
            deleteButton.disabled = deleteInput.value.trim() !== 'VERWIJDER';
        });
        deleteButton.addEventListener('click', () => {
            if (!pendingForm || deleteInput.value.trim() !== 'VERWIJDER') return;
            const form = pendingForm;
            authorizedForm = form;
            pendingForm = null;
            deleteDialog.close();
            form.requestSubmit();
        });
        deleteDialog.addEventListener('close', () => { pendingForm = null; });
        window.addEventListener('pageshow', () => {
            queryAll('form[data-confirm-delete] button[type="submit"]').forEach(button => {
                button.disabled = false;
                button.textContent = 'Verwijderen';
            });
        });

        queryAll('img').forEach(img => {
            const fallback = () => {
                img.hidden = true;
                const wrapper = img.parentElement;
                wrapper.classList.remove('has-image');
                const label = create('span', '?');
                label.setAttribute('aria-label', 'Profielfoto niet beschikbaar');
                wrapper.append(label);
            };
            img.addEventListener('error', fallback, { once: true });
            if (img.complete && img.naturalWidth === 0) fallback();
        });
        let printState = null;
        const beforePrint = () => {
            if (printState) return;
            printState = queryAll('details').map(details => [details, details.open]);
            grids.forEach(grid => {
                grid.rows.forEach(row => { row.hidden = !grid.filtered.includes(row); });
            });
            queryAll('details.mx-module').forEach(details => {
                details.open = queryAll('[data-grid-row]:not([hidden])', details).length > 0;
            });
        };
        const afterPrint = () => {
            if (!printState) return;
            printState.forEach(([details, open]) => { details.open = open; });
            printState = null;
            grids.forEach(grid => grid.apply());
        };
        window.addEventListener('beforeprint', beforePrint);
        window.addEventListener('afterprint', afterPrint);
        query('[data-print]').addEventListener('click', () => window.print());
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
})();
</script>
@endsection
