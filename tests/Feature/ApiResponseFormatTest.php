<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiResponseFormatTest extends TestCase
{
    public function test_api_validation_error_uses_standard_json_shape(): void
    {
        $response = $this->postJson('/api/chat/query', []);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ]);
    }

    public function test_api_not_found_uses_standard_json_shape(): void
    {
        $response = $this->getJson('/api/this-endpoint-does-not-exist');

        $response
            ->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Endpoint not found.',
            ]);
    }
}

