<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use Tests\TestCase;

class ShowEmployeeTest extends TestCase
{
    public function test_it_returns_a_single_employee_successfully(): void
    {
        $employee = Employee::first();

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
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
            ])
            ->assertJsonPath('data.id', $employee->id)
            ->assertJsonPath('data.name', $employee->name)
            ->assertJsonPath('data.last_name', $employee->last_name);
    }

    public function test_it_returns_not_found_for_nonexistent_employee(): void
    {
        $response = $this->getJson('/api/employees/99999');

        $response->assertStatus(404);
    }
}
