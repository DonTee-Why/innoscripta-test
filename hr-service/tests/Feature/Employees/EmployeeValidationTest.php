<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeValidationTest extends TestCase
{
    public function test_it_handles_string_numeric_salary_input_correctly_if_allowed(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => '75000',
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Jane',
                'last_name' => 'Smith',
            ]);

        $this->assertDatabaseHas('employees', [
            'name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $employee = Employee::where('name', 'Jane')->first();
        $this->assertEquals(75000, $employee->salary);
    }
}
