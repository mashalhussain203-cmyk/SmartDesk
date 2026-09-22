MASHAL AI V6 - PRODUCTIVITY STUDIO
==================================

Dit is een veilige uitbreiding bovenop jouw huidige V5.2.

NIEUW
-----
1. AI Dashboard
   URL: /ai-studio
   - aantallen chats/projecten/documenten
   - recente gesprekken
   - recente projecten
   - recente bestanden
   - 30-daagse agent-statistieken
   - provider/configuratiestatus

2. 16 AI Templates
   Onder andere:
   - Web research
   - Snel internet zoeken
   - Document uitleggen
   - Actiepunten uit document
   - Professionele e-mail
   - Tekst verbeteren
   - Vertalen NL/EN/Urdu
   - Code review
   - Laravel debug
   - Data-analyse
   - Plan maken
   - Opties vergelijken
   - Vergadering samenvatten
   - Social post
   - SEO landingspagina
   - Afbeeldingsprompt

   Een template opent /ai-chat, vult automatisch de prompt in en kiest de juiste
   AI-modus (plain/auto/web/research/code).

3. Globale zoekinterface
   - zoekt via jouw bestaande AiWorkspaceService
   - chats
   - projectdocumenten

4. PWA foundation
   - manifest.webmanifest
   - service worker
   - 192/512 Mashal AI app icons
   - install-knop wanneer de browser dat ondersteunt
   - POST/API requests worden bewust NIET offline gecachet

5. AI systeemstatus
   - Chat
   - Voice input
   - Vision
   - Azure Speech
   - Workspace
   Alleen configuratiestatus; geen geheime keys worden getoond.

INSTALLEREN
-----------
Pak de ZIP uit over:
C:\Users\Mashal\Downloads\laravel\laravel-app

Daarna:
cd C:\Users\Mashal\Downloads\laravel\laravel-app
powershell -ExecutionPolicy Bypass -File .\install-mashal-ai-v6.ps1

Controles:
php -l app\Http\Controllers\AiStudioController.php
php -l app\Http\Controllers\AiChatController.php
php -l config\ai-templates.php
php -l routes\ai-studio.php

composer dump-autoload
php artisan optimize:clear
php artisan view:clear
php artisan view:cache
php artisan route:list --name=ai.studio

Er zijn GEEN nieuwe database-migrations nodig.

TEST
----
1. Open /ai-studio
2. Klik template "Snel opzoeken"
3. Controleer of /ai-chat opent en prompt klaarstaat
4. Controleer of de modus op Internet staat
5. Klik template "Laravel fout oplossen"
6. Controleer of modus Code wordt gekozen
7. Zoek via dashboard naar een bekende chat/bestandsnaam
8. Controleer statuskaarten
9. Op ondersteunde mobiele browser: controleer Install app

GIT
---
git status
git add app/Http/Controllers/AiChatController.php
git add app/Http/Controllers/AiStudioController.php
git add config/ai-templates.php
git add resources/views/ai/chat.blade.php
git add resources/views/ai/dashboard.blade.php
git add routes/ai-studio.php
git add public/manifest.webmanifest
git add public/mashal-ai-sw.js
git add public/icons/mashal-ai-192.png
git add public/icons/mashal-ai-512.png
git add routes/web.php

git commit -m "Add Mashal AI V6 productivity studio"
git push origin main

BELANGRIJK
----------
Deze V6 voegt bewust geen nep-Gmail, agenda of Azure/OpenAI fallback toe.
Voor echte e-mail/agenda/provider-fallback zijn aparte providerconfiguraties en
credentials nodig. De bestaande V5.2 chat/workspace blijft de basis.
