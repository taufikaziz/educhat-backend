<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Ensure the landing page renders correctly.
     */
    public function test_landing_page_is_accessible_and_shows_main_content(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('EduChat', false)
            ->assertSee('Belajar Lebih Mudah dengan AI', false)
            ->assertSee(route('chat'), false);
    }
}
