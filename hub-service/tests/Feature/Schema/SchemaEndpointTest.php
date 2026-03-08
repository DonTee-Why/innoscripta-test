<?php

namespace Tests\Feature\Schema;

use App\Enums\Country;
use Tests\TestCase;

class SchemaEndpointTest extends TestCase
{
    public function test_it_returns_schema_for_valid_step_and_country(): void
    {
        $country = Country::USA;
        $response = $this->getJson("/api/schema/dashboard?country={$country}");

        $response->assertOk()
            ->assertJsonStructure([
                'country',
                'step_id',
                'widgets' => [
                    [
                        'id',
                        'type',
                        'title',
                        'data_source',
                        'realtime' => [
                            'channel',
                            'event',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'country' => $country,
                'step_id' => 'dashboard',
            ]);
    }

    public function test_it_requires_country_query_parameter(): void
    {
        $response = $this->getJson('/api/schema/dashboard');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }

    public function test_it_validates_country_query_parameter(): void
    {
        $response = $this->getJson('/api/schema/dashboard?country=France');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }
}
