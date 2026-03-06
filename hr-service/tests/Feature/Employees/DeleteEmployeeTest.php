<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use Tests\TestCase;

class DeleteEmployeeTest extends TestCase
{
    public function test_it_deletes_employee_successfully(): void
    {
        $employee = Employee::first();

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(204);
    }

    public function test_it_removes_deleted_employee_from_database(): void
    {
        $employee = Employee::first();
        $employeeId = $employee->id;

        $this->deleteJson("/api/employees/{$employeeId}");

        $this->assertDatabaseMissing('employees', [
            'id' => $employeeId,
        ]);
    }

    public function test_it_returns_no_content_after_successful_delete(): void
    {
        $employee = Employee::first();

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    public function test_it_returns_not_found_when_deleting_nonexistent_employee(): void
    {
        $response = $this->deleteJson('/api/employees/99999');

        $response->assertStatus(404);
    }
}
