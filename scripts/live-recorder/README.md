# SmartDesk – privé-opnames via Google Chrome op Railway

## Wat deze code doet

- Twee optionele, altijd actieve opname-workers: `knock1knock` en `emyii`, ieder in eigen Chrome/Chromium-venster.
- De worker bezoekt de bijbehorende Chaturbate-pagina en controleert ongeveer elke 60 seconden of de video daadwerkelijk afspeelt.
- Als video afspeelt, begint FFmpeg met een opname van het browservenster en de geselecteerde geluidsbron.
- De opname loopt door tot offline tweemaal is vastgesteld, de speler driemaal niet afspeelt, of de testlimiet is bereikt.
- Na afloop remuxt FFmpeg MKV naar MP4 en uploadt het videobestand in stukken van 2 MiB via een geheime Bearer-token naar de Laravel API van SmartDesk.
- De Laravel API zet het bestand in de **lokale privé-opslag**, waar uitsluitend bestaande SmartDesk-admins het onder `/live` kunnen bekijken, downloaden en verwijderen.
- Uploads worden bij netwerkproblemen opnieuw geprobeerd. Voltooide MP4's en achtergebleven MKV's worden bij een worker-herstart opnieuw opgepakt.

**De GitHub-code kan NIET zelf de Railway-services, secrets of volumes activeren.** Dat moet eenmalig in Railway gebeuren, waarna een **echte test tijdens een livestream** nodig blijft. Zonder die configuratie wordt er geen video opgenomen.

## Stap 1 — SmartDesk-webservice (bestaande Railway-service)

Laat de bestaande Laravel-startconfiguratie ongemoeid; de API-route `/api/internal/live-recordings` wordt bij de normale Laravel-deploy geladen.

Configureer in de **webservice**:
- `LIVE_RECORDER_SECRET` = een willekeurige, geheime waarde van minimaal 32 tekens. **Nooit in GitHub of op de client plaatsen.**
- Een Railway-volume op `/app/storage/app/private/live-recordings` voor blijvende MP4-opnames en status.json. Controleer het pad daadwerkelijk met de service; Nixpacks gebruikt doorgaans `/app`.
- Zorg dat de service genoeg opslag heeft. Een livestream van meerdere uren kan verschillende GB in beslag nemen.
- Bestaande `APP_KEY`, database, sessies en inlogsysteem blijven ongewijzigd.

De tijdelijke API-uploadstukjes staan onder `storage/app/private/live-recording-uploads` en worden na een geslaagde upload opgeruimd. De bestanden zijn nooit openbaar via `/storage`.

## Stap 2 — Aparte Railway-recorder voor iedere account

Maak een **nieuwe Railway-service vanuit dezelfde GitHub-repository en branch main**. Gebruik de Dockerfile op `scripts/live-recorder/Dockerfile`, via servicevariabele:

- `RAILWAY_DOCKERFILE_PATH=scripts/live-recorder/Dockerfile`
- `LIVE_ACCOUNT=knock1knock` (tweede service: `emyii`)
- `LIVE_RECORDER_SECRET=` exact dezelfde geheime waarde als de SmartDesk-webservice.
- `LIVE_RECORDER_INGEST_URL=https://JOUW-SMARTDESK-DOMEIN/api/internal/live-recordings`
  of het interne Railway-webserviceadres met de werkelijk gebruikte serverpoort: `http://SMARTDESK-SERVICE.railway.internal:PORT/api/internal/live-recordings`.

Verbind optioneel maar sterk aanbevolen een **eigen Railway-volume** met deze recorder op `/data`, voor onderbroken of nog niet geüploade opnames en de Chrome-profielen. De webservice en recorder **kunnen niet hetzelfde Railway-volume delen**.

Een recorder is een achtergrondproces en heeft geen publiek domein nodig. Railway herstart hem volgens zijn normale servicebeleid. Start eerst met één recorder en voeg de tweede pas toe zodra de eerste werkend is getest.

## Stap 3 — Live test

1. Start een eigen livestream op het bijpassende account.
2. Kijk als beheerder op `/live`. `ONBEKEND` betekent dat er geen verifieerbaar afspelende video is; het betekent NIET automatisch offline.
3. Voor een afzonderlijke korte test: stel `LIVE_TEST_SECONDS=30` tijdelijk in op de **recorder-service**. De recorder maakt dan één testopname van 30 seconden, uploadt hem en stopt. Verwijder de instelling voor doorlopende volledige opnames en herstart de service.
4. Controleer of status `OPNAME LOOPT`, daarna `BEZIG MET OPSLAAN` en uiteindelijk een privé-MP4 verschijnt. Test geluid, videobeeld en toegang als admin én als gewone bezoeker.
5. Als Chrome alleen een leeftijdscheck/login toont, moet die via het Chrome-profiel in de recorder eenmalig rechtsgeldig worden voltooid. Dit project omzeilt geen leeftijdscontroles, platformbeveiliging of toegangsregels.

## Belangrijke beperkingen

- Chaturbate kan automatische opname of toegang via browserautomatisering volgens eigen voorwaarden beperken. Controleer dat je eigen opnames op die manier zijn toegestaan.
- Videodetectie via zichtbare browserbeelden is **best effort**. Een gewijzigde site/player, CAPTCHA, advertentie, buffering of blokkering kan opname vertragen of verhinderen.
- Ook bij minuutcontroles gaat mogelijk het eerste stukje van de livestream verloren.
- De MP4 wordt pas na afloop zichtbaar. De bron is een 1280×720 browservenster; het is niet identiek aan de ruwe OBS-uitzending.
- Geluid is afhankelijk van werkende PulseAudio-audio in de browser. Dit moet echt worden getest.
- Bij verlies van opslagvolume kunnen video's verdwijnen. Maak back-ups en zorg voor voldoende schijfruimte.
- Opname is geen gegarandeerde end-to-end functie voordat de Railway-deploy en live tests zijn afgerond.

## GitHub-controles

De workflow `.github/workflows/live-recordings.yml` valideert PHP, JavaScript, de web-npm-installatie en de Laravel-tests voor de privébibliotheek en geauthenticeerde upload.
