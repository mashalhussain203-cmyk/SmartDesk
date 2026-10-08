# Urdu-vertaling (اردو) voor SmartDesk / Mashal Studio

Deze branch voegt een **Urdu-taalkeuze met rechts-naar-links-weergave (RTL)** toe, zonder route-, authenticatie- of databasecode aan te passen.

## Gebruik
- Open de website en klik op **اردو (Urdu)** linksonder.
- Via **Nederlands (NL)** schakel je terug.
- De keuze wordt in `localStorage` bewaard, zodat deze op andere pagina's blijft gelden.
- De drie Blade-layouts (`site-layout`, `admin-layout` en `app`) laden het taalbestand `public/js/smartdesk-urdu.js`.
- Bekende gebruikersgerichte teksten, labels en placeholders worden vervangen; onbekende teksten blijven intact.

## Wat is vertaald?
- Algemene navigatie, account- en beveiligingswoorden, inloggen, registratie en wachtwoordherstel.
- Kernteksten voor afbeeldingen uploaden/bewerken, AI, Gmail, productcatalogus, winkelwagen en checkout.
- Zichtbare tekstonderdelen van de pagina's **Over ons**, **Contact**, **Gebruiksvoorwaarden** en **Privacybeleid**.

## Nog niet volledig vertaald
Dit is een **eerste uitvoerbare vertaalbasis**, nog **geen volledig afgeronde lokalisatie** van de hele applicatie.

- E-mails en PHP/backend-foutmeldingen worden server-side gegenereerd en worden door deze browservertaling niet geraakt.
- Teksten in browser-`alert()`-, `confirm()`-dialoogvensters en in JavaScript-strings die niet op de pagina worden weergegeven, worden niet automatisch vertaald.
- Onbekende of nieuwe teksten blijven Nederlands of Engels totdat er een gecontroleerde Urdu-vertaling wordt toegevoegd.
- Eventuele juridische teksten moeten voor publicatie door een deskundige worden nagekeken.
- RTL-verschillen op complexe editor-, chat- en beheerschermen moeten visueel in een draaiende browser worden getest.

## Technische keuzes
De vertaallaag vervangt uitsluitend tekstnodes en toegestane tekst-attributen (`placeholder`, `title`, `aria-label`, `alt`). Gebruikersinvoer, wachtwoorden, HTML-tags, code, routes en data worden niet vertaald. Nieuwe interface-elementen worden via een `MutationObserver` verwerkt; na taalwisseling wordt de oorspronkelijke tekst teruggezet.

## Handmatige controles
1. Open homepage, inloggen, registratie, account, afbeeldingen en admin.
2. Schakel naar Urdu en controleer of labels en teksten in RTL staan.
3. Schakel terug naar Nederlands en controleer of de oorspronkelijke tekst terugkeert.
4. Refresh en controleer of de taalkeuze bewaard blijft.
5. Controleer formulieren, validatie, afbeelding-editor en chat op regressies.
6. Controleer extra de vertaling en lay-out van privacybeleid en gebruiksvoorwaarden.
