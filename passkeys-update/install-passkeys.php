<?php

/** Run from the Laravel project: php passkeys-update/install-passkeys.php --check */
declare(strict_types=1);

$root = getcwd();
$dryRun = in_array('--check', $argv, true);
if (! is_file($root.'/artisan')) {
    fwrite(STDERR, "Open eerst de terminal in je Laravel-projectmap (waar artisan staat).\n");
    exit(1);
}

function insertOnce(string $source, string $marker, string $anchor, string $replacement, string $file): string
{
    if (str_contains($source, $marker)) {
        return $source;
    }
    if (substr_count($source, $anchor) !== 1) {
        throw new RuntimeException("Onverwachte indeling in {$file}. Er is niets gewijzigd. Stuur dit bestand voor aanpassing.");
    }

    return str_replace($anchor, $replacement, $source);
}

try {
    $writes = [];
    $read = static function (string $path) use ($root): string {
        $contents = file_get_contents($root.'/'.$path);
        if ($contents === false) {
            throw new RuntimeException('Kan bestand niet lezen: '.$path);
        }

        return str_replace("\r\n", "\n", $contents);
    };

    $path = 'routes/web.php';
    $source = $read($path);
    if (str_contains($source, '?>')) {
        throw new RuntimeException('routes/web.php heeft een afsluitende PHP-tag; laat deze update eerst aanpassen.');
    }
    $writes[$path] = str_contains($source, "require __DIR__.'/passkeys.php';")
        ? $source : rtrim($source)."\n\nrequire __DIR__.'/passkeys.php';\n";

    $path = 'bootstrap/providers.php';
    $source = preg_replace('/^\xEF\xBB\xBF/', '', $read($path));
    $writes[$path] = insertOnce($source, 'PasskeyServiceProvider::class', '];',
        "    App\\Providers\\PasskeyServiceProvider::class,\n];", $path);

    $path = 'resources/views/site/login.blade.php';
    $anchor = '<div class="login-tabs"';
    $writes[$path] = insertOnce($read($path), "'passkeyMode' => 'login'", $anchor,
        "@include('partials.passkeys', ['passkeyMode' => 'login'])\n\n                        ".$anchor, $path);

    $path = 'resources/views/site/security.blade.php';
    $anchor = '<div class="security-shell">';
    $writes[$path] = insertOnce($read($path), "'passkeyMode' => 'manage'", $anchor,
        $anchor."\n        @include('partials.passkeys', ['passkeyMode' => 'manage'])", $path);

    foreach (['app/Models/User.php', 'app/Services/LoginSecurityService.php'] as $path) {
        $source = $read($path);
        $anchor = '$allowedProviders = [';
        if (! preg_match('/\$allowedProviders\s*=\s*\[\s*\x27passkey\x27/', $source)) {
            $source = insertOnce($source, "'passkey',", $anchor, $anchor."\n            'passkey',", $path);
        }
        if ($path === 'app/Models/User.php') {
            $source = insertOnce($source, "'passkey' => 'Passkey'", "'password' => 'Wachtwoord',",
                "'passkey' => 'Passkey',\n            'password' => 'Wachtwoord',", $path);
            $source = insertOnce($source, "'passkey' => 'lock'", "'password' => 'lock',",
                "'passkey' => 'lock',\n            'password' => 'lock',", $path);
        }
        $writes[$path] = $source;
    }
    $path = 'app/Models/LoginActivity.php';
    $writes[$path] = insertOnce($read($path), "'passkey' => 'Passkey'", "'password' => 'E-mail + wachtwoord',",
        "'passkey' => 'Passkey',\n            'password' => 'E-mail + wachtwoord',", $path);

    $sourceRoot = __DIR__.'/files/';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($sourceRoot)));
        $writes[$relative] = file_get_contents($file->getPathname());
        if (is_file($root.'/'.$relative) && file_get_contents($root.'/'.$relative) !== $writes[$relative]) {
            throw new RuntimeException('Bestand bestaat al met andere inhoud: '.$relative.'. Er is niets gewijzigd.');
        }
    }

    echo "Gecontroleerd: ".count($writes)." bestanden.\n";
    if ($dryRun) {
        echo "Controle geslaagd. Er is niets gewijzigd.\n";
        exit(0);
    }

    $backup = __DIR__.'/backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(3));
    $originals = [];
    foreach ($writes as $path => $contents) {
        $target = $root.'/'.$path;
        $originals[$path] = is_file($target) ? file_get_contents($target) : null;
        if ($originals[$path] !== null) {
            $destination = $backup.'/'.$path;
            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0775, true);
            }
            if (file_put_contents($destination, $originals[$path]) === false) {
                throw new RuntimeException('Backup mislukt. Er is niets gewijzigd.');
            }
        }
    }
    $completed = [];
    try {
        foreach ($writes as $path => $contents) {
            $target = $root.'/'.$path;
            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0775, true);
            }
            $completed[] = $path;
            if (file_put_contents($target, $contents) !== strlen($contents)) {
                throw new RuntimeException('Schrijven mislukt: '.$path);
            }
        }
    } catch (Throwable $exception) {
        foreach ($completed as $path) {
            if ($originals[$path] === null) {
                @unlink($root.'/'.$path);
            } else {
                file_put_contents($root.'/'.$path, $originals[$path]);
            }
        }
        throw $exception;
    }
    echo "Passkeycode toegevoegd. Backup: {$backup}\n";
    echo "Voer nu de Composer-, migratie- en teststappen uit START-HIER.txt uit.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}
