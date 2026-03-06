<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use Tests\TestCase;

class UpdateEmployeeTest extends TestCase
{
    public function test_it_updates_a_usa_employee_successfully(): void
    {
        $employee = Employee::where('country', 'USA')->first();

        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 80000,
            'ssn' => '123-45-6789',
            'address' => '789 New St, Boston, MA',
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Jane',
                'last_name' => 'Smith',
                'salary' => '80000.00',
            ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Jane',
            'salary' => 80000,
        ]);
    }

    public function test_it_updates_a_germany_employee_successfully(): void
    {
        $employee = Employee::where('country', 'Germany')->first();

        $data = [
            'name' => 'Klaus',
            'last_name' => 'Schmidt',
            'country' => 'Germany',
            'salary' => 70000,
            'tax_id' => 'DE987654321',
            'goal' => 'New goal for 2025',
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Klaus',
                'goal' => 'New goal for 2025',
            ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'tax_id' => 'DE987654321',
        ]);
    }

    public function test_it_returns_updated_employee_resource_after_successful_update(): void
    {
        $employee = Employee::first();

        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => $employee->country,
            'salary' => 99999,
        ];

        if ($employee->country === 'USA') {
            $data['ssn'] = $employee->ssn;
            $data['address'] = $employee->address;
        } else {
            $data['tax_id'] = $employee->tax_id;
            $data['goal'] = $employee->goal;
        }

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

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
            ->assertJsonPath('data.salary', '99999.00');
    }

    public function test_it_persists_updated_employee_to_database(): void
    {
        $employee = Employee::first();
        $originalName = $employee->name;

        $data = [
            'name' => 'UpdatedName',
            'last_name' => $employee->last_name,
            'country' => $employee->country,
            'salary' => $employee->salary,
        ];

        if ($employee->country === 'USA') {
            $data['ssn'] = $employee->ssn;
            $data['address'] = $employee->address;
        } else {
            $data['tax_id'] = $employee->tax_id;
            $data['goal'] = $employee->goal;
        }

        $this->putJson("/api/employees/{$employee->id}", $data);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'UpdatedName',
        ]);
        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
            'name' => $originalName,
        ]);
    }

    public function test_it_validates_salary_if_present_on_update(): void
    {
        $employee = Employee::first();

        $data = $this->getValidUpdatePayload($employee);
        $data['salary'] = 'invalid';

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salary']);
    }

    public function test_it_validates_salary_is_greater_than_zero_on_update(): void
    {
        $employee = Employee::first();

        $data = $this->getValidUpdatePayload($employee);
        $data['salary'] = 0;

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salary']);
    }

    public function test_it_requires_goal_if_country_is_germany_and_goal_is_expected_on_update(): void
    {
        $employee = Employee::where('country', 'Germany')->first();

        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => 'Germany',
            'salary' => $employee->salary,
            'tax_id' => $employee->tax_id,
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['goal']);
    }

    public function test_it_requires_ssn_if_country_is_usa_and_ssn_is_expected_on_update(): void
    {
        $employee = Employee::where('country', 'USA')->first();

        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => 'USA',
            'salary' => $employee->salary,
            'address' => $employee->address,
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ssn']);
    }

    public function test_it_returns_not_found_when_updating_nonexistent_employee(): void
    {
        $response = $this->putJson('/api/employees/99999', [
            'name' => 'Test',
            'last_name' => 'User',
            'country' => 'USA',
            'salary' => 50000,
            'ssn' => '123-45-6789',
            'address' => '123 Test St',
        ]);

        $response->assertStatus(404);
    }

    public function test_it_handles_updating_only_country_specific_fields_for_usa(): void
    {
        $employee = Employee::where('country', 'USA')->first();

        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => 'USA',
            'salary' => $employee->salary,
            'ssn' => '999-88-7766',
            'address' => 'New Address Only',
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.ssn', '999-88-7766')
            ->assertJsonPath('data.address', 'New Address Only');
    }

    public function test_it_handles_updating_only_country_specific_fields_for_germany(): void
    {
        $employee = Employee::where('country', 'Germany')->first();

        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => 'Germany',
            'salary' => $employee->salary,
            'tax_id' => 'DE111222333',
            'goal' => 'Updated Germany goal only',
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.tax_id', 'DE111222333')
            ->assertJsonPath('data.goal', 'Updated Germany goal only');
    }

    public function test_it_handles_null_optional_fields_correctly(): void
    {
        $employee = Employee::where('country', 'USA')->first();

        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => 'USA',
            'salary' => $employee->salary,
            'ssn' => $employee->ssn,
            'address' => $employee->address,
            'tax_id' => null,
            'goal' => null,
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200);
    }

    public function test_it_does_not_break_when_update_payload_contains_only_one_field(): void
    {
        $employee = Employee::first();
        $originalData = $employee->toArray();

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'name' => 'OnlyNameChanged',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'OnlyNameChanged');

        $employee->refresh();
        $this->assertEquals($originalData['last_name'], $employee->last_name);
        $this->assertEquals($originalData['country'], $employee->country);
    }

    public function test_it_preserves_existing_fields_when_partial_update_is_sent(): void
    {
        $employee = Employee::where('country', 'USA')->first();
        $originalSsn = $employee->ssn;
        $originalAddress = $employee->address;

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => 'USA',
            'salary' => 85000,
            'ssn' => $employee->ssn,
            'address' => $employee->address,
        ]);

        $response->assertStatus(200);

        $employee->refresh();
        $this->assertEquals(85000, $employee->salary);
        $this->assertEquals($originalSsn, $employee->ssn);
        $this->assertEquals($originalAddress, $employee->address);
    }

    private function getValidUpdatePayload(Employee $employee): array
    {
        $data = [
            'name' => $employee->name,
            'last_name' => $employee->last_name,
            'country' => $employee->country,
            'salary' => $employee->salary,
        ];

        if ($employee->country === 'USA') {
            $data['ssn'] = $employee->ssn;
            $data['address'] = $employee->address;
        } else {
            $data['tax_id'] = $employee->tax_id;
            $data['goal'] = $employee->goal;
        }

        return $data;
    }
}
