<?php

namespace Tests\Unit\Services;

use App\Contracts\EventPublisher;
use App\Models\Employee;
use App\Services\EmployeeService;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    protected EmployeeService $employeeService;

    public function setUp(): void
    {
        parent::setUp();
        $this->employeeService = $this->app->make(EmployeeService::class);
    }

    public function test_employee_service_creates_employee_successfully(): void
    {
        $data = [
            'name' => 'Jane',
            'last_name' => 'Smith',
            'country' => 'USA',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '456 Oak Ave',
        ];

        $employee = $this->employeeService->create($data);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals('Jane', $employee->name);
        $this->assertEquals('Smith', $employee->last_name);
        $this->assertEquals('USA', $employee->country);
        $this->assertEquals(75000, $employee->salary);
        $this->assertDatabaseHas('employees', ['name' => 'Jane']);
    }

    public function test_employee_service_updates_employee_successfully(): void
    {
        $employee = Employee::first();

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

        $updated = $this->employeeService->update($employee, $data);

        $this->assertInstanceOf(Employee::class, $updated);
        $this->assertEquals('UpdatedName', $updated->name);
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => 'UpdatedName']);
    }

    public function test_employee_service_computes_changed_fields_correctly(): void
    {
        $this->mock(EventPublisher::class)->shouldReceive('publish')
            ->once()
            ->withArgs(function (string $routingKey, array $payload) {
                $changedFields = $payload['data']['changed_fields'];
                $this->assertContains('name', $changedFields);
                $this->assertContains('salary', $changedFields);
                return true;
            });

        $employee = Employee::first();

        $data = [
            'name' => 'NewName',
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

        $service = $this->app->make(EmployeeService::class);
        $service->update($employee, $data);
    }

    public function test_employee_service_deletes_employee_successfully(): void
    {
        $employee = Employee::first();
        $employeeId = $employee->id;

        $result = $this->employeeService->delete($employee);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('employees', ['id' => $employeeId]);
    }
}
