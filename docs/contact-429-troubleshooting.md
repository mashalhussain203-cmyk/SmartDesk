# Contactpagina: HTTP 429 op Railway

## Wat de code doet

- `GET /contact` is een publieke route **zonder Laravel-rate limit**.
- `POST /contact` verwerkt het formulier en heeft spambeveiliging.
- De eerdere `throttle:5,1` (vijf inzendingen per minuut vanaf hetzelfde IP-adres) is vervangen door `throttle:contact-form`:
  - maximaal **30 verzoeken per minuut per IP-adres** (ook bij gedeelde mobiele of kantoor-IP's);
  - maximaal **5 inzendingen per 10 minuten per browsersessie**;
  - maximaal **5 inzendingen per uur per geldig e-mailadres**, intern gehasht.
- Wie te vaak op verzenden klikt, wordt teruggestuurd naar de contactpagina met een vriendelijke melding, geen generieke 429-foutpagina. De invoer wordt hersteld.

De server blijft beschermd tegen overmatig verzenden. Laat deze limieten dus actief.

## Belangrijk: GET /contact geeft 429, nog voordat je een formulier instuurt?

Deze **GET**-route wordt niet door de Laravel-contactlimiet geraakt. Controleer eerst of de 429 bij Railway ontstaat, voordat de aanvraag Laravel bereikt:

1. Open in je browser **https://mashalhussain.up.railway.app/contact** en vergelijk met **/up** en **/over-ons**.
2. Inspecteer de response in je browser (Network-tab) of gebruik:
   ```sh
   curl -i https://mashalhussain.up.railway.app/contact
   ```
3. Bij `server: railway-hikari` met een korte `rate limited` tekst en **geen aanvraag in Laravel/Railway HTTP-service-logs**, kan het Railway Edge/WAF zijn. Een Railway-edge-header alleen bewijst overigens niet dat de edge de 429 heeft gemaakt; ook doorgestuurde antwoorden passeren daar.
4. Controleer **Railway → jouw service → Settings → Edge → Under Attack Mode**. Staat die aan terwijl er geen aanval gaande is, schakel die uit via **Deactivate**. Hetzelfde kan via `railway waf under-attack status` / `railway waf under-attack disable`.
5. Als Under Attack Mode uit staat, inspecteer Railway HTTP-logs en eventuele Edge-regels en zoek op het exacte tijdstip van de 429. Deel het trace-ID indien je Railway-support nodig hebt.

## Na GitHub-update

De wijziging moet daadwerkelijk door Railway zijn gedeployed. Controleer de actieve deployment/commit. Bij oude route- of configuratiecache op de server: voer `php artisan optimize:clear` uit bij een deployment of herstart de service.

## Tests

`php artisan test --filter=ContactRateLimitTest`

Test de pagina vanuit een echte browser nadat Railway de nieuwe deployment actief heeft gemaakt. Deze repositorywijziging alleen kan **geen 429 in Railway's edge-proxy oplossen** wanneer het verzoek Laravel niet bereikt.
