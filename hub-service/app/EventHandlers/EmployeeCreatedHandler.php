<?php

namespace App\EventHandlers;

use App\Contracts\EventHandlerInterface;
use App\Infrastructure\Broadcasting\Broadcaster;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EmployeeCreatedHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly EmployeeCacheRepository $employeeCacheRepository,
        private readonly ChecklistCacheRepository $checklistCacheRepository,
        private readonly Broadcaster $broadcaster,
    ) {}

    public function handle(array $event): void
    {
        $country = $event['country'] ?? null;
        $data = $event['data'] ?? null;
        $employeeId = $data['employee_id'] ?? null;
        $employee = $data['employee'] ?? null;
        $changedFields = $data['changed_fields'] ?? [];

        if (! \is_string($country) || $country === '') {
            throw new InvalidArgumentException('Country is missing from EmployeeCreated event.');
        }

        if (!\is_numeric($employeeId)) {
            throw new InvalidArgumentException('Employee ID is missing from EmployeeCreated event.');
        }

        if (!\is_array($employee)) {
            throw new InvalidArgumentException('Employee snapshot is missing from EmployeeCreated event.');
        }

        $employeeId = (int) $employeeId;

        $this->employeeCacheRepository->upsert(
            country: $country,
            employeeId: $employeeId,
            employee: $employee,
        );

        $this->employeeCacheRepository->invalidatePaginatedLists($country);
        $this->checklistCacheRepository->invalidate($country);

        $this->broadcaster->broadcastEmployeeCreated(
            country: $country,
            employeeId: $employeeId,
            payload: [
                'event_type' => 'EmployeeCreated',
                'employee_id' => $employeeId,
                'changed_fields' => $changedFields,
                'employee' => $employee,
            ]
        );

        $this->broadcaster->broadcastChecklistUpdated(
            country: $country,
            payload: [
                'event_type' => 'ChecklistInvalidated',
                'country' => $country,
                'employee_id' => $employeeId,
                'reason' => 'employee_created',
            ]
        );

        Log::info('EmployeeCreated event handled successfully', [
            'event_id' => $event['event_id'] ?? null,
            'country' => $country,
            'employee_id' => $employeeId,
        ]);
    }
}
