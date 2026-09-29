$ErrorActionPreference = "Stop"

Write-Host "== PHP syntax =="
$phpFiles = @(
    "app/Http/Controllers/AdminLiveChatController.php",
    "app/Http/Controllers/LiveChatController.php",
    "app/Http/Controllers/MailgunLiveChatWebhookController.php",
    "app/Services/LiveChatService.php",
    "app/Services/LiveChatEmailService.php",
    "app/Mail/LiveChatThreadMail.php",
    "config/live-chat-email.php",
    "routes/live-chat.php",
    "database/migrations/2026_09_30_000001_add_email_handoff_to_live_chat.php"
)

foreach ($file in $phpFiles) {
    php -l $file
}

Write-Host ""
Write-Host "== JavaScript syntax =="
node --check public/js/admin-live-chat.js
node --check public/js/live-chat.js

Write-Host ""
Write-Host "== Laravel =="
php artisan optimize:clear
php artisan route:list | findstr live-chat

Write-Host ""
Write-Host "Voer nu de migration uit als de routes goed zijn:"
Write-Host "php artisan migrate"
Write-Host ""
Write-Host "Daarna:"
Write-Host "php artisan test --compact tests/Feature/LiveChatTest.php"
