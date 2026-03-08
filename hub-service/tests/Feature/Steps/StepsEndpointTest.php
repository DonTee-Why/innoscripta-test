<?php

namespace Tests\Feature\Steps;

use App\Enums\Country;
use Tests\TestCase;

class StepsEndpointTest extends TestCase
{
    public function test_it_returns_steps_for_valid_country(): void
    {
        $country = Country::USA;
        $response = $this->getJson("/api/steps?country={$country}");

        $response->assertOk()
            ->assertExactJson([
                'country' => $country,
                'steps' => [
                    [
                        'id' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'layout-dashboard',
                        'order' => 1,
                        'path' => '/dashboard',
                    ],
                    [
                        'id' => 'employees',
                        'label' => 'Employees',
                        'icon' => 'users',
                        'order' => 2,
                        'path' => '/employees',
                    ],
                ],
            ]);
    }

    public function test_it_requires_country_query_parameter(): void
    {
        $response = $this->getJson('/api/steps');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }

    public function test_it_validates_country_query_parameter(): void
    {
        $response = $this->getJson('/api/steps?country=France');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }
}
