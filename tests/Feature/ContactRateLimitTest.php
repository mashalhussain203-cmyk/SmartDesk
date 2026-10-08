<?php

namespace Tests\Feature;

use App\Services\GmailLiveChatService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ContactRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'session.driver' => 'array',
            'app.locale' => 'nl',
        ]);

        // Avoid contacting Gmail when the controller is resolved during tests.
        $this->mock(GmailLiveChatService::class);
    }

    public function test_contact_page_is_accessible_to_guests_without_a_get_rate_limit(): void
    {
        $this->assertNotContains(
            'throttle:contact-form',
            Route::getRoutes()->getByName('contact')->gatherMiddleware()
        );

        $this->get('/contact')
            ->assertOk()
            ->assertSee('name="first_name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="message"', false);
    }

    public function test_rate_limited_form_submission_redirects_to_contact_instead_of_a_429_page(): void
    {
        // Invalid submissions are used to avoid sending real Gmail messages.
        $invalidData = [
            'first_name' => '',
            'last_name' => '',
            'email' => 'visitor@example.com',
            'message' => '',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('contact'))
                ->post(route('contact.send'), $invalidData)
                ->assertRedirect(route('contact'));
        }

        $this->post(route('contact.send'), $invalidData)
            ->assertRedirect(route('contact'))
            ->assertSessionHas('error');

        // Even after a blocked POST, the contact page itself must still open.
        $this->get(route('contact'))
            ->assertOk();
    }

    public function test_contact_submission_uses_the_named_limiter(): void
    {
        $route = Route::getRoutes()->getByName('contact.send');

        $this->assertContains('throttle:contact-form', $route->gatherMiddleware());
        $this->assertNotContains('throttle:5,1', $route->gatherMiddleware());
    }
}
