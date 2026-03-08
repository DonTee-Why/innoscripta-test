<?php

namespace Tests\Feature\Employees;

use App\Services\EmployeeQueryService;
use Mockery\MockInterface;
use Tests\TestCase;

class EmployeesEndpointTest extends TestCase
{
    /** @var EmployeeQueryService&MockInterface */
    private MockInterface $employeeQueryService;

    public function setUp(): void
    {
        parent::setUp();

        $this->employeeQueryService = $this->mock(EmployeeQueryService::class);
    }

    public function test_it_returns_paginated_employees_for_valid_country(): void
    {
        $payload = [
            'country' => 'USA',
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'text'],
                ['key' => 'salary', 'label' => 'Salary', 'type' => 'currency'],
                ['key' => 'ssn', 'label' => 'SSN', 'type' => 'masked-text'],
            ],
            'data' => [
                [
                    'id' => 1,
                    'name' => 'John',
                    'last_name' => 'Doe',
                    'salary' => 120000,
                    'ssn' => '***-**-6789',
                    'country' => 'USA',
                ],
            ],
            'meta' => [
                'page' => 2,
                'per_page' => 5,
                'total' => 11,
                'last_page' => 3,
            ],
        ];

        $this->employeeQueryService
            ->shouldReceive('getByCountry')
            ->once()
            ->with('USA', 2, 5)
            ->andReturn($payload);

        $response = $this->getJson('/api/employees?country=USA&page=2&per_page=5');

        $response->assertOk()->assertExactJson($payload);
    }

    public function test_it_requires_country_query_parameter(): void
    {
        $response = $this->getJson('/api/employees');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }

    public function test_it_validates_country_query_parameter_is_supported(): void
    {
        $response = $this->getJson('/api/employees?country=France');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }

    public function test_it_validates_page_and_per_page_parameters(): void
    {
        $response = $this->getJson('/api/employees?country=USA&page=0&per_page=101');

        $response->assertStatus(422)->assertJsonValidationErrors(['page', 'per_page']);
    }
}
