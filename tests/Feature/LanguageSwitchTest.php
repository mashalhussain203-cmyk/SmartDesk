<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'array',
            'app.locale' => 'nl',
        ]);
    }

    public function test_guest_sees_language_buttons_in_the_site_header(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('name="locale" value="nl"', false)
            ->assertSee('name="locale" value="ur"', false)
            ->assertSee('Nederlands', false)
            ->assertSee('اردو', false)
            ->assertSee('class="expert-mobile-language"', false);

        // The locale switch is rendered twice: once in the header
        // and once directly inside the mobile drawer.
        $response = $this->get(route('about'));
        $this->assertSame(2, substr_count($response->getContent(), 'name="locale" value="ur"'));
        $this->assertSame(2, substr_count($response->getContent(), 'name="locale" value="nl"'));
    }

    public function test_guest_can_switch_to_urdu_and_back_without_leaving_the_page(): void
    {
        $page = route('about');

        $this->withHeader('Referer', $page)
            ->post(route('language.switch'), ['locale' => 'ur'])
            ->assertRedirect($page);

        $this->assertSame('ur', session('site_locale'));

        $this->get($page)
            ->assertOk()
            ->assertSee('lang="ur"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('زبان منتخب کریں', false);

        $this->withHeader('Referer', $page)
            ->post(route('language.switch'), ['locale' => 'nl'])
            ->assertRedirect($page);

        $this->assertSame('nl', session('site_locale'));

        $this->get($page)
            ->assertOk()
            ->assertSee('lang="nl"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Taal kiezen', false);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->from(route('about'))
            ->post(route('language.switch'), ['locale' => 'unsupported'])
            ->assertRedirect(route('about'))
            ->assertSessionHasErrors('locale');
    }

    public function test_language_switch_never_redirects_to_external_referer(): void
    {
        $this->withHeader('Referer', 'https://example.net/phishing')
            ->post(route('language.switch'), ['locale' => 'ur'])
            ->assertRedirect(route('home'));
    }

    public function test_about_page_shows_urdu_translations_instead_of_dutch(): void
    {
        $this->withSession(['site_locale' => 'ur'])
            ->get(route('about'))
            ->assertOk()
            ->assertSee('lang="ur"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('ہمارے بارے', false)
            ->assertSee('Mashal Studio ایک ڈیجیٹل پلیٹ فارم ہے', false)
            ->assertSee('ذاتی معلومات کے استعمال سے متعلق', false);
    }

    public function test_contact_and_legal_navigation_are_localized(): void
    {
        $this->withSession(['site_locale' => 'ur'])
            ->get(route('contact'))
            ->assertOk()
            ->assertSee('ہم سے رابطہ کریں', false)
            ->assertSee('رازداری', false);
    }

}
