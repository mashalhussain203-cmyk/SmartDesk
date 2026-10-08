<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContactFormReliabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate request throttling from the production database in tests.
        config([
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);
    }

    public function test_contact_get_has_a_fresh_token_and_is_not_cached(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'private, no-store, no-cache, max-age=0')
            ->assertSee('name="_token"', false);

        $route = app('router')->getRoutes()->getByName('contact.send');

        $this->assertNotNull($route);
        $this->assertContains('throttle:contact-form', $route->gatherMiddleware());
    }

    public function test_expired_contact_session_shows_a_new_form_and_helpful_message(): void
    {
        $this->get(route('contact', ['session_expired' => '1']))
            ->assertOk()
            ->assertSee('Je sessie was verlopen. Het bericht is niet verzonden.')
            ->assertSee('name="_token"', false);
    }

    public function test_contact_get_is_not_blocked_by_submission_rate_limit(): void
    {
        // Validation failures count towards the POST limiter, without
        // sending any real messages through the external Gmail service.
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('contact.send'), [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'message' => '',
            ])->assertRedirect();
        }

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }
}
