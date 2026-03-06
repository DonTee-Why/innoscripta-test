<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use Tests\TestCase;

class ListEmployeesTest extends TestCase
{
    public function test_it_returns_a_list_of_employees(): void
    {
        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
            ]);

        $this->assertGreaterThanOrEqual(2, \count($response->json('data')));
    }

    public function test_it_returns_employees_in_expected_resource_format(): void
    {
        $response = $this->getJson('/api/employees');

        $response->assertStatus(200);

        $employees = $response->json('data');
        $this->assertIsArray($employees);

        foreach ($employees as $employee) {
            $this->assertArrayHasKey('id', $employee);
            $this->assertArrayHasKey('name', $employee);
            $this->assertArrayHasKey('last_name', $employee);
            $this->assertArrayHasKey('full_name', $employee);
            $this->assertArrayHasKey('country', $employee);
            $this->assertArrayHasKey('salary', $employee);
            $this->assertArrayHasKey('created_at', $employee);
            $this->assertArrayHasKey('updated_at', $employee);
        }
    }

    public function test_it_returns_empty_list_when_no_employees_exist(): void
    {
        Employee::query()->delete();

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonPath('data', []);
    }
}
