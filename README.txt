MASHAL AI V5 WORKSPACE EXPERT
=============================

V5 bouwt bovenop de werkende V4.3 stack:
Groq Chat + Internet/Research + Code Interpreter + Vision + bestanden +
Live Voice + Azure Speech + NL/EN/Urdu + uitgebreide desktop/mobiele UI.

NIEUW
-----
- Database chatgeschiedenis en cross-device synchronisatie
- Projecten met eigen instructies
- Permanente projectbestanden
- Project knowledge retrieval/RAG zonder extra vector-database
- Projectbronnen zichtbaar onder antwoorden
- Handmatig persoonlijk geheugen
- Accountbrede chat/document search
- Read-only deellinks
- Chat export naar Markdown
- Agent-run logging voor later gebruik/admin-dashboard
- Bestaande localStorage chats worden bij eerste sync naar de server geïmporteerd
- Alle nieuwe workspace endpoints zijn user-scoped en auth-protected

BESTANDEN
---------
Nieuw:
app/Http/Controllers/AiWorkspaceController.php
app/Models/AiProject.php
app/Models/AiConversation.php
app/Models/AiMessage.php
app/Models/AiDocument.php
app/Models/AiMemory.php
app/Models/AiShareLink.php
app/Models/AiAgentRun.php
app/Services/AiWorkspaceService.php
config/ai-workspace.php
database/migrations/2026_09_22_000001_create_ai_projects_table.php
database/migrations/2026_09_22_000002_create_ai_conversations_table.php
database/migrations/2026_09_22_000003_create_ai_messages_table.php
database/migrations/2026_09_22_000004_create_ai_documents_table.php
database/migrations/2026_09_22_000005_create_ai_memories_table.php
database/migrations/2026_09_22_000006_create_ai_share_links_table.php
database/migrations/2026_09_22_000007_create_ai_agent_runs_table.php
resources/views/ai/shared.blade.php
routes/ai-workspace.php
install-mashal-ai-v5.ps1

Bijgewerkt:
app/Http/Controllers/AiChatController.php
app/Services/GroqChatService.php
app/Services/ChatFileReaderService.php
app/Services/GroqVisionService.php
config/groq-chat.php
config/groq-vision.php
resources/views/ai/chat.blade.php

INSTALLATIE
-----------
Pak ZIP uit over:
C:\Users\Mashal\Downloads\laravel\laravel-app

Daarna:
cd C:\Users\Mashal\Downloads\laravel\laravel-app
powershell -ExecutionPolicy Bypass -File .\install-mashal-ai-v5.ps1

Controle:
php -l app\Services\AiWorkspaceService.php
php -l app\Http\Controllers\AiWorkspaceController.php
php -l app\Http\Controllers\AiChatController.php
php -l app\Services\GroqChatService.php
php -l app\Services\ChatFileReaderService.php
php -l app\Services\GroqVisionService.php
php -l config\ai-workspace.php
php -l routes\ai-workspace.php

composer dump-autoload
php artisan migrate
php artisan optimize:clear
php artisan view:clear
php artisan view:cache

php artisan route:list --name=ai.chat
php artisan route:list --name=ai.workspace

RAILWAY
-------
Bestaande Groq- en Azure-variabelen blijven staan.

Optioneel/aanbevolen:
AI_WORKSPACE_ENABLED=true
AI_WORKSPACE_DISK=local
AI_WORKSPACE_RAG_ENABLED=true
AI_WORKSPACE_RAG_SCAN_DOCS=40
AI_WORKSPACE_RAG_MAX_SOURCES=5
AI_WORKSPACE_RAG_EXCERPT_CHARS=3500
AI_WORKSPACE_RAG_MAX_CONTEXT_CHARS=14000
AI_WORKSPACE_MEMORY_ENABLED=true
AI_WORKSPACE_MEMORY_MAX_ITEMS=20
AI_WORKSPACE_MEMORY_MAX_CHARS=5000
AI_WORKSPACE_SHARING_ENABLED=true
AI_WORKSPACE_SHARE_DAYS=30

PERSISTENT STORAGE
------------------
Database chats/projectgegevens blijven in jouw database.
De fysieke projectbestanden staan op Laravel Storage::disk('local').

Op Railway moet je voor productie een persistent Volume gebruiken voor de
Laravel storage directory, of AI_WORKSPACE_DISK koppelen aan S3-compatible
object storage. Anders kunnen fysieke uploads bij een container-redeploy
verdwijnen, terwijl database records nog bestaan.

RAG
---
V5 gebruikt direct werkende lexical/keyword retrieval:
- geen nieuwe provider
- geen vector database nodig
- relevante bestanden worden gerankt op vraagtermen
- maximaal ingestelde excerpts gaan mee als context
- bestandbronnen worden apart naar de UI teruggestuurd

Voor een zeer grote kennisbank kan later embeddings/vector search worden
toegevoegd zonder het project/chat/datamodel opnieuw te bouwen.

PRIVACY / VEILIGHEID
--------------------
- Workspace endpoints vereisen login.
- Queries zijn altijd user_id scoped.
- Bestanddownloads zijn user-scoped.
- Documenttekst wordt expliciet als onbevoegd gebruikersmateriaal naar de AI
  gestuurd; document-instructies mogen systeeminstructies niet overschrijven.
- Memory is handmatig; niets wordt stilletjes als persoonlijk geheugen bewaard.
- Share links zijn alleen-lezen en verlopen standaard.

EXTERNE INTEGRATIES
-------------------
Niet nep geïmplementeerd zonder provider/credentials:
- Gmail/Outlook lezen of verzenden
- Google/Microsoft Calendar wijzigen
- native push notifications
- externe image-generationprovider
- vector embeddings voor miljoenen documenten

De V5-basis is erop voorbereid, maar zulke functies vereisen afzonderlijke
OAuth/providerconfiguratie.

GIT
---
git status
git check-ignore .env

git add app/Http/Controllers/AiChatController.php
git add app/Http/Controllers/AiWorkspaceController.php
git add app/Models
git add app/Services/AiWorkspaceService.php
git add app/Services/GroqChatService.php
git add app/Services/ChatFileReaderService.php
git add app/Services/GroqVisionService.php
git add config/ai-workspace.php
git add config/groq-chat.php
git add config/groq-vision.php
git add database/migrations
git add resources/views/ai/chat.blade.php
git add resources/views/ai/shared.blade.php
git add routes/ai-workspace.php
git add routes/web.php

git commit -m "Add Mashal AI V5 workspace projects memory and persistent knowledge"
git push origin main
