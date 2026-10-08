<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UrduTranslationCoverageTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 2);
    }

    public function test_urdu_dictionary_is_valid_and_covers_main_public_pages(): void
    {
        $raw = file_get_contents($this->root().'/lang/ur.json');
        $this->assertIsString($raw);

        $translations = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($translations);

        foreach ([
            'Over ons',
            'Wat Mashal Studio biedt',
            'Contact',
            'Gebruiksvoorwaarden',
            'Privacybeleid',
            'Inloggen',
            'Registreren',
            'Wachtwoord herstellen',
            'Taal kiezen',
        ] as $key) {
            $this->assertArrayHasKey($key, $translations);
            $this->assertNotSame($key, $translations[$key]);
        }
    }

    public function test_standalone_mail_and_admin_views_offer_urdu_language_switching(): void
    {
        foreach ([
            'resources/views/layouts/admin-layout.blade.php',
            'resources/views/gmail/inbox.blade.php',
            'resources/views/gmail/show.blade.php',
        ] as $path) {
            $view = file_get_contents($this->root().'/'.$path);
            $this->assertIsString($view, $path);
            $this->assertStringContainsString("partials.language-switcher", $view, $path);
            $this->assertStringContainsString("app()->getLocale() === 'ur'", $view, $path);
        }
    }

    public function test_client_side_urdu_bundle_is_shipped_on_every_main_layout(): void
    {
        $bundle = file_get_contents($this->root().'/public/js/smartdesk-urdu.js');
        $this->assertIsString($bundle);
        $this->assertStringContainsString('window.smartDeskTranslate', $bundle);

        foreach ([
            'resources/views/layouts/site-layout.blade.php',
            'resources/views/layouts/admin-layout.blade.php',
            'resources/views/gmail/inbox.blade.php',
            'resources/views/gmail/show.blade.php',
        ] as $path) {
            $view = file_get_contents($this->root().'/'.$path);
            $this->assertIsString($view);
            $this->assertStringContainsString("js/smartdesk-urdu.js", $view, $path);
        }
    }
}
