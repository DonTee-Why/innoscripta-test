<?php

namespace Tests\Feature\Employees;

use App\Contracts\EventPublisher;
use App\Models\Employee;
use Tests\TestCase;

final class EventPublishingTest extends TestCase
{
    public function test_it_publishes_employee_created_with_correct_routing_key(): void
    {
        $this->mock(EventPublisher::class)
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function (string $routingKey, array $payload) {
                $this->assertSame('employees.USA.created', $routingKey);
                $this->assertSame('EmployeeCreated', $payload['event_type']);
                $this->assertSame('USA', $payload['country']);
                $this->assertArrayHasKey('event_id', $payload);
                $this->assertArrayHasKey('timestamp', $payload);
                $this->assertIsInt($payload['data']['employee_id']);
                $this->assertContains('name', $payload['data']['changed_fields']);
                $this->assertContains('last_name', $payload['data']['changed_fields']);
                $this->assertContains('salary', $payload['data']['changed_fields']);
                $this->assertContains('ssn', $payload['data']['changed_fields']);
                $this->assertContains('address', $payload['data']['changed_fields']);
                $this->assertSame(75000, (int) $payload['data']['employee']['salary']);
                return true;
            });

        $res = $this->postJson('/api/employees', [
            'country' => 'USA',
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '123 Main St, New York, NY',
        ]);

        $res->assertCreated();
    }

    public function test_it_publishes_employee_updated_with_changed_fields(): void
    {
        $employee = Employee::where('country', 'USA')->first();

        $this->mock(EventPublisher::class)
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function (string $routingKey, array $payload) use ($employee) {
                $this->assertSame('employees.USA.updated', $routingKey);
                $this->assertSame('EmployeeUpdated', $payload['event_type']);
                $this->assertSame('USA', $payload['country']);
                $this->assertArrayHasKey('event_id', $payload);
                $this->assertArrayHasKey('timestamp', $payload);
                $this->assertSame($employee->id, $payload['data']['employee_id']);
                $this->assertContains('salary', $payload['data']['changed_fields']);
                $this->assertSame(80000, (int) $payload['data']['employee']['salary']);
                return true;
            });

        $res = $this->putJson("/api/employees/{$employee->id}", [
            'salary' => 80000,
        ]);

        $res->assertOk();
    }

    public function test_it_publishes_employee_updated_with_full_employee_snapshot(): void
    {
        $employee = Employee::where('country', 'Germany')->first();

        $this->mock(EventPublisher::class)
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function (string $routingKey, array $payload) use ($employee) {
                $this->assertSame('employees.Germany.updated', $routingKey);
                $this->assertSame('EmployeeUpdated', $payload['event_type']);
                $this->assertSame($employee->id, $payload['data']['employee_id']);
                $this->assertSame('Germany', $payload['data']['employee']['country']);
                $this->assertSame('DE111222333', $payload['data']['employee']['tax_id']);
                $this->assertSame('Updated Germany goal', $payload['data']['employee']['goal']);
                return true;
            });

        $res = $this->putJson("/api/employees/{$employee->id}", [
            'tax_id' => 'DE111222333',
            'goal' => 'Updated Germany goal',
        ]);

        $res->assertOk();
    }

    public function test_it_publishes_employee_deleted_with_expected_event_envelope(): void
    {
        $employee = Employee::where('country', 'USA')->first();
        $employeeId = $employee->id;

        $this->mock(EventPublisher::class)
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function (string $routingKey, array $payload) use ($employeeId) {
                $this->assertSame('employees.USA.deleted', $routingKey);
                $this->assertSame('EmployeeDeleted', $payload['event_type']);
                $this->assertSame('USA', $payload['country']);
                $this->assertArrayHasKey('event_id', $payload);
                $this->assertArrayHasKey('timestamp', $payload);
                $this->assertSame($employeeId, $payload['data']['employee_id']);
                $this->assertSame([], $payload['data']['changed_fields']);
                $this->assertSame($employeeId, $payload['data']['employee']['id']);

                return true;
            });

        $res = $this->deleteJson("/api/employees/{$employeeId}");

        $res->assertNoContent();
    }
}
