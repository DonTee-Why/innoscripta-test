<?php

namespace App\EventHandlers;

use App\Contracts\EventHandlerInterface;
use App\Infrastructure\Broadcasting\Broadcaster;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EmployeeDeletedHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly EmployeeCacheRepository $employeeCacheRepository,
        private readonly ChecklistCacheRepository $checklistCacheRepository,
        private readonly Broadcaster $broadcaster,
    ) {}

    public function handle(array $event): void
    {
        $country = strtolower($event['country']) ?? null;
        $data = $event['data'] ?? null;
        $employeeId = $data['employee_id'] ?? null;
        $employee = $data['employee'] ?? null;

        if (! \is_string($country) || $country === '') {
            throw new InvalidArgumentException('Country is missing from EmployeeDeleted event.');
        }

        if (! \is_numeric($employeeId)) {
            throw new InvalidArgumentException('Employee ID is missing from EmployeeDeleted event.');
        }

        $employeeId = (int) $employeeId;

        $this->employeeCacheRepository->delete(
            country: $country,
            employeeId: $employeeId,
        );

        $this->employeeCacheRepository->invalidatePaginatedLists($country);
        $this->checklistCacheRepository->invalidate($country);

        $this->broadcaster->broadcastEmployeeDeleted(
            country: $country,
            employeeId: $employeeId,
            payload: [
                'event_type' => 'EmployeeDeleted',
                'employee_id' => $employeeId,
                'employee' => $employee,
            ]
        );

        $this->broadcaster->broadcastChecklistUpdated(
            country: $country,
            payload: [
                'event_type' => 'ChecklistInvalidated',
                'country' => $country,
                'employee_id' => $employeeId,
                'reason' => 'employee_deleted',
            ]
        );

        Log::info('EmployeeDeleted event handled successfully', [
            'event_id' => $event['event_id'] ?? null,
            'country' => $country,
            'employee_id' => $employeeId,
        ]);
    }
}
