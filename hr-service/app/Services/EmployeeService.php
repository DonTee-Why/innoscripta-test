<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
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
        return Employee::create($data);
    }

    /**
     * Update an existing employee.
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee->fresh();
    }

    /**
     * Delete an employee.
     */
    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
