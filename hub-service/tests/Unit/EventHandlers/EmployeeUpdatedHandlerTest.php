<?php

namespace Tests\Unit\EventHandlers;

use App\EventHandlers\EmployeeUpdatedHandler;
use App\Infrastructure\Broadcasting\Broadcaster;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class EmployeeUpdatedHandlerTest extends TestCase
{
    public function test_it_upserts_employee_snapshot_when_employee_updated_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once()->with('USA', 10, ['id' => 10]);
        $checklistCache->shouldReceive('invalidate')->once()->with('USA');
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once()->with('USA');
        $broadcaster->shouldReceive('broadcastEmployeeUpdated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeUpdatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_invalidates_paginated_employee_lists_when_employee_updated_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once()->with('USA');
        $broadcaster->shouldReceive('broadcastEmployeeUpdated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeUpdatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_invalidates_checklist_cache_when_employee_updated_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $checklistCache->shouldReceive('invalidate')->once()->with('USA');
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $broadcaster->shouldReceive('broadcastEmployeeUpdated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeUpdatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_broadcasts_employee_updated_when_employee_updated_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $broadcaster->shouldReceive('broadcastEmployeeUpdated')->once()->with('USA', 10, Mockery::type('array'));
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeUpdatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_broadcasts_checklist_updated_when_employee_updated_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $broadcaster->shouldReceive('broadcastEmployeeUpdated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once()->with('USA', Mockery::type('array'));

        (new EmployeeUpdatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_throws_when_country_is_missing_in_employee_updated_event(): void
    {
        $handler = new EmployeeUpdatedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country is missing from EmployeeUpdated event.');

        $handler->handle(['data' => ['employee_id' => 10, 'employee' => ['id' => 10]]]);
    }

    public function test_it_throws_when_employee_id_is_missing_in_employee_updated_event(): void
    {
        $handler = new EmployeeUpdatedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee ID is missing from EmployeeUpdated event.');

        $handler->handle(['country' => 'USA', 'data' => ['employee' => ['id' => 10]]]);
    }

    public function test_it_throws_when_employee_snapshot_is_missing_in_employee_updated_event(): void
    {
        $handler = new EmployeeUpdatedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee snapshot is missing from EmployeeUpdated event.');

        $handler->handle(['country' => 'USA', 'data' => ['employee_id' => 10]]);
    }

    private function validEvent(): array
    {
        return [
            'event_id' => 'evt-2',
            'country' => 'USA',
            'data' => [
                'employee_id' => 10,
                'employee' => ['id' => 10],
                'changed_fields' => ['address'],
            ],
        ];
    }
}
