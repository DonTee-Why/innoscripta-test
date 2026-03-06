<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use Tests\TestCase;

class CreateEmployeeTest extends TestCase
{
    public function test_it_creates_a_usa_employee_successfully(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave, Los Angeles, CA',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Jane',
                'last_name' => 'Smith',
                'country' => 'USA',
                'ssn' => '123-45-6789',
                'address' => '456 Oak Ave, Los Angeles, CA',
            ]);
    }

    public function test_it_creates_a_germany_employee_successfully(): void
    {
        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 65000,
            'tax_id' => 'DE123456789',
            'goal' => 'Improve team efficiency',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Klaus',
                'last_name' => 'Schmidt',
                'country' => 'Germany',
                'tax_id' => 'DE123456789',
                'goal' => 'Improve team efficiency',
            ]);
    }

    public function test_it_returns_created_employee_resource_after_successful_creation(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave, Los Angeles, CA',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'last_name',
                    'full_name',
                    'country',
                    'salary',
                    'ssn',
                    'address',
                    'tax_id',
                    'goal',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_it_persists_created_employee_to_database(): void
    {
        $initialCount = Employee::count();

        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave, Los Angeles, CA',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('employees', [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
        ]);

        $this->assertEquals($initialCount + 1, Employee::count());
    }

    public function test_it_requires_country_on_create(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }

    public function test_it_requires_name_on_create(): void
    {
        $data = [
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_requires_last_name_on_create(): void
    {
        $data = [
            'name' => 'Jane',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['last_name']);
    }

    public function test_it_requires_salary_on_create(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salary']);
    }

    public function test_it_requires_salary_to_be_numeric_on_create(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 'not-a-number',
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salary']);
    }

    public function test_it_requires_salary_to_be_greater_than_zero_on_create(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 0,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salary']);
    }

    public function test_it_requires_ssn_when_country_is_usa(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ssn']);
    }

    public function test_it_requires_address_when_country_is_usa(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['address']);
    }

    public function test_it_allows_goal_to_be_null_when_country_is_usa(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201);
    }

    public function test_it_allows_tax_id_to_be_null_when_country_is_usa(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201);
    }

    public function test_it_requires_goal_when_country_is_germany(): void
    {
        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 65000,
            'tax_id' => 'DE123456789',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['goal']);
    }

    public function test_it_requires_tax_id_when_country_is_germany(): void
    {
        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 65000,
            'goal' => 'Improve team efficiency',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tax_id']);
    }

    public function test_it_allows_ssn_to_be_null_when_country_is_germany(): void
    {
        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 65000,
            'tax_id' => 'DE123456789',
            'goal' => 'Improve team efficiency',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201);
    }

    public function test_it_allows_address_to_be_null_when_country_is_germany(): void
    {
        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 65000,
            'tax_id' => 'DE123456789',
            'goal' => 'Improve team efficiency',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201);
    }

    public function test_it_rejects_unsupported_country_on_create(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'France',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }

    public function test_it_requires_tax_id_to_match_germany_format(): void
    {
        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 65000,
            'tax_id' => '123456789',
            'goal' => 'Improve team efficiency',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tax_id']);
    }
}
