<?php

namespace Tests\Unit\EventHandlers;

use App\EventHandlers\EmployeeDeletedHandler;
use App\Infrastructure\Broadcasting\Broadcaster;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class EmployeeDeletedHandlerTest extends TestCase
{
    public function test_it_deletes_employee_snapshot_when_employee_deleted_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('delete')->once()->with('USA', 10);
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once()->with('USA');
        $checklistCache->shouldReceive('invalidate')->once()->with('USA');
        $broadcaster->shouldReceive('broadcastEmployeeDeleted')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeDeletedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_invalidates_paginated_employee_lists_when_employee_deleted_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('delete')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once()->with('USA');
        $checklistCache->shouldReceive('invalidate')->once();
        $broadcaster->shouldReceive('broadcastEmployeeDeleted')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeDeletedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_invalidates_checklist_cache_when_employee_deleted_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('delete')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $checklistCache->shouldReceive('invalidate')->once()->with('USA');
        $broadcaster->shouldReceive('broadcastEmployeeDeleted')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeDeletedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_broadcasts_employee_deleted_when_employee_deleted_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('delete')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $broadcaster->shouldReceive('broadcastEmployeeDeleted')->once()->with('USA', 10, Mockery::type('array'));
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeDeletedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_broadcasts_checklist_updated_when_employee_deleted_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('delete')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $broadcaster->shouldReceive('broadcastEmployeeDeleted')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once()->with('USA', Mockery::type('array'));

        (new EmployeeDeletedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_throws_when_country_is_missing_in_employee_deleted_event(): void
    {
        $handler = new EmployeeDeletedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country is missing from EmployeeDeleted event.');

        $handler->handle(['data' => ['employee_id' => 10]]);
    }

    public function test_it_throws_when_employee_id_is_missing_in_employee_deleted_event(): void
    {
        $handler = new EmployeeDeletedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee ID is missing from EmployeeDeleted event.');

        $handler->handle(['country' => 'USA', 'data' => []]);
    }

    private function validEvent(): array
    {
        return [
            'event_id' => 'evt-3',
            'country' => 'USA',
            'data' => [
                'employee_id' => 10,
                'employee' => ['id' => 10],
            ],
        ];
    }
}
