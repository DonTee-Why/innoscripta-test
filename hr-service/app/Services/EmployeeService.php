<?php

namespace App\Services;

use App\Contracts\EventPublisher;
use App\Models\Employee;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(private EventPublisher $eventPublisher) {}
    /**
     * Get a paginated list of employees.
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Employee::query()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new employee.
     */
    public function create(array $data): Employee
    {
        $employee = Employee::create($data);

        $employeeData = [
            'employee_id' => $employee->id,
            'changed_fields' => array_keys($data),
            'employee' => $employee->toArray(),
        ];

        $this->publishEvent(
            $employee->country,
            'EmployeeCreated',
            $employeeData
        );
        return $employee;
    }

    /**
     * Update an existing employee.
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->fill($data);
        $changedFields = array_keys($employee->getDirty());

        DB::beginTransaction();

        try {
            $employee->save();
            DB::commit();

            $employeeData = [
                'employee_id' => $employee->id,
                'changed_fields' => $changedFields,
                'employee' => $employee->fresh()->toArray(),
            ];

            $this->publishEvent(
                $employee->country,
                'EmployeeUpdated',
                $employeeData
            );

            return $employee->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an employee.
     */
    public function delete(Employee $employee): bool
    {
        DB::beginTransaction();
        try {
            $employeeData = [
                'employee_id' => $employee->id,
                'changed_fields' => [],
                'employee' => $employee->toArray(),
            ];

            $employee->delete();
            DB::commit();

            $this->publishEvent(
                $employee->country,
                'EmployeeDeleted',
                $employeeData
            );

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function publishEvent(string $country, string $eventType, array $data): void
    {
        $routingKey = $this->mapEventTypeToRoutingKey($eventType, $country);
        $payload = [
            'event_type' => $eventType,
            'event_id' => Str::uuid()->toString(),
            'timestamp' => now()->toIso8601String(),
            'country' => $country,
            'data' => $data,
        ];

        $this->eventPublisher->publish($routingKey, $payload);
    }

    private function mapEventTypeToRoutingKey(string $eventType, string $country): string
    {
        return match ($eventType) {
            'EmployeeCreated' => "employees.{$country}.created",
            'EmployeeUpdated' => "employees.{$country}.updated",
            'EmployeeDeleted' => "employees.{$country}.deleted",
            default => throw new Exception("Invalid event type: {$eventType}"),
        };
    }
}
