# Contactformulier op Railway: 419 en 429

## Problemen en wijzigingen

- `GET /contact` blijft buiten het verzendlimiet. Het formulier krijgt een actuele CSRF-token en `Cache-Control: private, no-store, no-cache, max-age=0` zodat HTML met oude tokens niet wordt gecachet.
- `POST /contact` houdt **CSRF-verificatie aan** en gebruikt `throttle:contact-form` in plaats van het oude `throttle:5,1` per IP-adres.
- In plaats van een te strenge gedeelde-IP-limiet gebruikt het formulier:
  - 30 POST-pogingen per minuut per IP-adres,
  - 5 POST-pogingen per 10 minuten per browsersessie,
  - 5 POST-pogingen per uur per e-mailadres (gehasht in de limietsleutel).
- Wanneer een **419** optreedt op `POST /contact`, stuurt Laravel de bezoeker met HTTP 303 naar een **nieuw contactformulier** met een duidelijke waarschuwing dat de eerdere inzending niet is verwerkt. Dit omzeilt geen CSRF en verstuurt het bericht niet opnieuw.
- Bij te veel verzendingen verschijnt een nette foutmelding op de contactpagina.

## Belangrijk: een 419 kan alsnog terugkomen als sessies niet persistent zijn

De meest voorkomende oorzaak van een blijvende 419 op Railway is dat de browser na een GET niet dezelfde sessie behoudt bij de POST. **De GitHub-code alleen kan dit niet oplossen als de Railway-variabelen onjuist zijn.**

Controleer in Railway > Project > webservice > **Variables**:

1. **`APP_URL=https://mashalhussain.up.railway.app`** (precies het gebruikte domein en HTTPS).
2. **`APP_KEY`**: één reeds gegenereerde, **vaste** geheime Laravel-sleutel. Verander deze niet bij iedere deploy of tussen replica's. **Deel deze sleutel nooit** in chat, screenshots of GitHub.
3. **`SESSION_DRIVER=database`** alleen wanneer er een **persistente** database met een `sessions`-tabel is (de standaard Laravel-migratie kan die tabel aanmaken). Als je een tijdelijke SQLite-containerdatabase gebruikt, schakel dan over op een persistente database of een correct geconfigureerde Redis-sessiestore.
4. **`SESSION_DOMAIN`**: laat deze variabele **weg** voor het Railway-subdomein. Gebruik in elk geval niet `.railway.app`; een gedeeld hostingdomein mag geen brede cookie-domain-instelling krijgen.
5. **`SESSION_SECURE_COOKIE=true`**, **`SESSION_SAME_SITE=lax`** en **`SESSION_PATH=/`** voor deze HTTPS-site.
6. Zorg dat browsers sessiecookies mogen opslaan en dat meerdere app-replica's dezelfde `APP_KEY` én sessiestore gebruiken.
7. Herstart/deploy na variabelewijzigingen. Wis zo nodig configuratiecache met `php artisan config:clear`. Zorg dat `php artisan migrate --force` is uitgevoerd voor de sessietabel.

**Zet geen CSRF-uitzondering op het contactformulier.** De verificatie beschermt de POST tegen aanvragen vanaf andere sites.

## Controleren na deployment

- Open `https://mashalhussain.up.railway.app/contact` opnieuw via een nieuw browsertabblad.
- Controleer in je browser dat `GET /contact` 200 geeft én een sessiecookie opslaat.
- Controleer of een geldige formulier-POST werkt. Raadpleeg bij een 419 de browser-ontwikkelaarstools (Cookie / Set-Cookie en Requests), en bij een 429 de limieten in Railway / Laravel-logs.
- Een oude openstaande pagina met een token uit een eerdere deploy kan één keer verlopen; vernieuw die pagina.
- Voer uit: `php artisan test --filter=ContactFormReliabilityTest`. Dit is een geautomatiseerde regressiecheck; controleer daarnaast de echte website en uitgaande Gmail-bevestigingen.

## Vermijd verkeerde interpretatie

Een 419 op `POST /contact` is meestal een sessie-/CSRF-probleem. Een 429 kan van de Laravel POST-limiter **of** van een extern platform komen. Bij een 419/429 op een kale `GET /contact` moet Railway/edge-proxy-logging worden bekeken, want Laravel past op die GET geen contactformulier-throttle toe.
