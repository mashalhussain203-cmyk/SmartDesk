$ErrorActionPreference = "Stop"

$phpFiles = @(
    "app/Http/Controllers/AdminLiveChatController.php",
    "app/Http/Controllers/GmailLiveChatOAuthController.php",
    "app/Services/GmailApiClient.php",
    "app/Services/GmailLiveChatInboxService.php",
    "app/Services/LiveChatEmailService.php",
    "config/live-chat-email.php",
    "database/migrations/2026_09_30_000002_add_gmail_threading_to_live_chat.php",
    "database/migrations/2026_09_30_000003_add_email_title_to_live_chat.php",
    "routes/live-chat.php",
    "tests/Feature/LiveChatTest.php"
)

foreach ($file in $phpFiles) {
    if (-not (Test-Path $file)) {
        throw "Ontbreekt: $file"
    }

    php -l $file
    if ($LASTEXITCODE -ne 0) {
        throw "PHP syntaxfout: $file"
    }
}

node --check public/js/admin-live-chat.js
if ($LASTEXITCODE -ne 0) {
    throw "JavaScript syntaxfout: public/js/admin-live-chat.js"
}

if (-not (Test-Path "resources/views/mail/thread.blade.php")) {
    throw "Premium mail-template ontbreekt."
}

php artisan route:list | findstr /I "gmail/connect gmail/callback email-sync email-handoff"

Write-Host ""
Write-Host "OK - Gmail thread + onderwerp + titel package staat correct."
