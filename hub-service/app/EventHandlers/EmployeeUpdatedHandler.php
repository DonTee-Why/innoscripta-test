<?php

namespace App\EventHandlers;

use App\Contracts\EventHandlerInterface;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EmployeeUpdatedHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly EmployeeCacheRepository $employeeCacheRepository,
        private readonly ChecklistCacheRepository $checklistCacheRepository,
        // private readonly Broadcaster $broadcaster,
    ) {}

    public function handle(array $event): void
    {
        $country = $event['country'] ?? null;
        $data = $event['data'] ?? null;
        $employeeId = $data['employee_id'] ?? null;
        $employee = $data['employee'] ?? null;
        $changedFields = $data['changed_fields'] ?? [];

        if (! \is_string($country) || $country === '') {
            throw new InvalidArgumentException('Country is missing from EmployeeUpdated event.');
        }

        if (! is_numeric($employeeId)) {
            throw new InvalidArgumentException('Employee ID is missing from EmployeeUpdated event.');
        }

        if (! \is_array($employee)) {
            throw new InvalidArgumentException('Employee snapshot is missing from EmployeeUpdated event.');
        }

        // 1. Update cached employee snapshot
        $this->employeeCacheRepository->upsert(
            country: $country,
            employeeId: (int) $employeeId,
            employee: $employee,
        );

        // 2. Invalidate downstream cache entries affected by employee updates
        $this->checklistCacheRepository->invalidate($country);
        $this->employeeCacheRepository->invalidatePaginatedLists($country);

        // 3. Broadcast real-time updates
        // $this->broadcaster->broadcastEmployeeUpdated(
        //     country: $country,
        //     employeeId: (int) $employeeId,
        //     payload: [
        //         'event_type' => 'EmployeeUpdated',
        //         'employee_id' => (int) $employeeId,
        //         'changed_fields' => $changedFields,
        //         'employee' => $employee,
        //     ]
        // );

            // $this->broadcaster->broadcastChecklistUpdated(
            //     country: $country,
            //     payload: [
            //         'event_type' => 'ChecklistInvalidated',
            //         'country' => $country,
            //         'employee_id' => (int) $employeeId,
            //     ]
            // );

        Log::info('EmployeeUpdated event handled successfully', [
            'event_id' => $event['event_id'] ?? null,
            'country' => $country,
            'employee_id' => (int) $employeeId,
            'changed_fields' => $changedFields,
        ]);
    }
}
