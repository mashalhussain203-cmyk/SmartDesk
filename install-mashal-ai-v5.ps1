param(
    [string]$ProjectRoot = "."
)

$ErrorActionPreference = "Stop"

$root = (Resolve-Path $ProjectRoot).Path
$web = Join-Path $root "routes\web.php"

if (-not (Test-Path $web)) {
    throw "routes\web.php niet gevonden. Start dit script in de Laravel projectmap."
}

$backup = "$web.v5-backup"

if (-not (Test-Path $backup)) {
    Copy-Item $web $backup
    Write-Host "Backup gemaakt: $backup"
}

$content = Get-Content $web -Raw
$line = "require __DIR__.'/ai-workspace.php';"

if ($content -notmatch [regex]::Escape($line)) {
    Add-Content -Path $web -Value "`r`n// Mashal AI Workspace V5`r`n$line`r`n"
    Write-Host "Workspace routes toegevoegd aan routes\web.php"
}
else {
    Write-Host "Workspace routes waren al toegevoegd."
}

Write-Host ""
Write-Host "Volgende commando's:"
Write-Host "php artisan migrate"
Write-Host "php artisan optimize:clear"
Write-Host "php artisan view:clear"
Write-Host "php artisan view:cache"
Write-Host "php artisan route:list --name=ai.workspace"
