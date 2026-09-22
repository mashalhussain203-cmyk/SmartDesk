param(
    [string]$ProjectRoot = "."
)

$ErrorActionPreference = "Stop"

$root = (Resolve-Path $ProjectRoot).Path
$web = Join-Path $root "routes\web.php"

if (-not (Test-Path $web)) {
    throw "routes\web.php niet gevonden. Start dit script vanuit je Laravel-project."
}

$routeLine = "require __DIR__.'/ai-studio.php';"
$content = Get-Content $web -Raw

if ($content -notmatch [regex]::Escape($routeLine)) {
    Add-Content -Path $web -Value "`r`n// Mashal AI Studio V6`r`n$routeLine`r`n"
    Write-Host "V6 Studio routes toegevoegd."
}
else {
    Write-Host "V6 Studio routes bestaan al."
}

Write-Host ""
Write-Host "Voer nu uit:"
Write-Host "php -l app\Http\Controllers\AiStudioController.php"
Write-Host "php -l app\Http\Controllers\AiChatController.php"
Write-Host "php -l config\ai-templates.php"
Write-Host "php -l routes\ai-studio.php"
Write-Host "composer dump-autoload"
Write-Host "php artisan optimize:clear"
Write-Host "php artisan view:clear"
Write-Host "php artisan view:cache"
Write-Host "php artisan route:list --name=ai.studio"
