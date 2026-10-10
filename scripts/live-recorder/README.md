# SmartDesk – automatische privé livestreamopnames

De uitbreiding neemt via een zelfstandig draaiende Google Chrome-recorder video en geluid op van knock1knock of emyii. Geen Chaturbate-API. De videospeler moet normaal toegankelijk zijn; de implementatie omzeilt geen toegang, leeftijdsbevestiging, DRM of platformrestricties.

## Bouwstenen

1. Laravel SmartDesk: /live is afgeschermd met de bestaande login en is_admin-controle. Streaming, downloaden en verwijderen blijven via admin-only routes lopen.
2. Railway private storage bucket: langdurige opnames staan niet op de 500 MB applicatievolume maar als een MP4 per uitzending in de bucket, onder recordings/<account>/<filename>.mp4. De browser krijgt nooit bucket-sleutels of publieke S3-URL's.
3. Railway recorder workers: één onafhankelijke Docker-service per account, gebouwd uit scripts/live-recorder/Dockerfile. Die start Chromium, FFmpeg, Xvfb en PulseAudio. De worker controleert ongeveer elke minuut of video in Chrome speelt en streamt beeld plus geluid rechtstreeks via S3 multipart-upload naar de bucket. Daardoor is geen grote lokale opnameschijf nodig.
4. Live status in status/<account>.json en privé MP4's verschijnen na het afronden van de multipart-upload automatisch op /live.

## Extra livestreamaccounts

Naast de primaire Chrome-workers lopen aparte HLS-monitoren voor extra accounts.
De bestaande `emyii`-service bewaakt `cutefacebigass`, `ricasashaa` en `gimbobar`.
De `knock1knock`-service neemt uitsluitend het primaire account op.
Elke account heeft een eigen statusbestand en S3-map. Een extra Railway-service is
voor `ricasashaa` niet nodig zolang de bestaande worker voldoende capaciteit heeft.

## Railway instellingen

Maak een private Railway Storage Bucket, bijvoorbeeld genaamd smartdesk-live-private. Zet zowel op SmartDesk als op de twee recorder services deze vijf omgevingsvariabelen met Railway bucket-reference variables: LIVE_S3_ENDPOINT, LIVE_S3_BUCKET, LIVE_S3_REGION, LIVE_S3_ACCESS_KEY_ID en LIVE_S3_SECRET_ACCESS_KEY. Gebruik de referenties van de aangemaakte bucket; schrijf de geheime sleutel nooit in GitHub-code.

Maak twee Railway services met GitHub-bron mashalhussain203-cmyk/SmartDesk, branch main, Dockerfile-pad scripts/live-recorder/Dockerfile. Zet LIVE_ACCOUNT=knock1knock op de eerste en LIVE_ACCOUNT=emyii op de tweede. Laat geen publiek domein aan de workers koppelen. Laat de workers continu draaien; gebruik geen Railway cronjob (minimale interval vijf minuten) of slaapstand.

Voor een eenmalige opname van 30 seconden: LIVE_TEST_SECONDS=30. Verwijder deze instelling voor normale, volledige streams. Een worker stopt na twee expliciete offlinecontroles of drie opeenvolgende onzekere playbackcontroles om eindeloze lege bestanden te voorkomen.

## Belangrijke grenzen

- Alle privacy- en downloadcontroles vinden plaats in Laravel. De privébucket is niet openbaar.
- Een live pagina of tekst met 'LIVE' is niet genoeg: er moet daadwerkelijk een video afspelen in Chrome. Zonder afspeelbare livestream wordt niet opgenomen.
- Chaturbate kan leeftijdsbevestiging, login en autoplay blokkeren. De code omzeilt deze controles niet en kan alleen worden gebruikt wanneer de website toestemming geeft om op te nemen.
- De opslagbucket en twee continu actieve Railway worker-services maken extra gebruikskosten. Railway Buckets worden per GB-maand belast en worker CPU, RAM en egress afzonderlijk.
- Bij een workercrash of uploadstoring vóór het afronden kan de lopende MP4 verloren gaan. Volledige crashbestendigheid vereist een uitgebreider segment-/herstelmechanisme.
- De eerste 0-60 seconden plus de browserlaadtijd kunnen ontbreken. Na een stream moet de MP4 met geluid tijdens een echte opname worden getest.

## Tests

De GitHub workflow in .github/workflows/live-recordings.yml installeert de Node recorder-afhankelijkheden, controleert JavaScript en PHP syntax en test de adminbeveiliging. Dat vervangt niet een end-to-end test met een echte livestream.
