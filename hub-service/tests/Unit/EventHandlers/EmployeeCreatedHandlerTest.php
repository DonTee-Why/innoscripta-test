<?php

namespace Tests\Unit\EventHandlers;

use App\EventHandlers\EmployeeCreatedHandler;
use App\Infrastructure\Broadcasting\Broadcaster;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class EmployeeCreatedHandlerTest extends TestCase
{
    public function test_it_upserts_employee_snapshot_when_employee_created_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once()->with('USA', 10, ['id' => 10]);
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once()->with('USA');
        $checklistCache->shouldReceive('invalidate')->once()->with('USA');
        $broadcaster->shouldReceive('broadcastEmployeeCreated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeCreatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_invalidates_paginated_employee_lists_when_employee_created_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once()->with('USA');
        $checklistCache->shouldReceive('invalidate')->once();
        $broadcaster->shouldReceive('broadcastEmployeeCreated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeCreatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_invalidates_checklist_cache_when_employee_created_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $checklistCache->shouldReceive('invalidate')->once()->with('USA');
        $broadcaster->shouldReceive('broadcastEmployeeCreated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeCreatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_broadcasts_employee_created_when_employee_created_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $broadcaster->shouldReceive('broadcastEmployeeCreated')->once()->with('USA', 10, Mockery::type('array'));
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once();

        (new EmployeeCreatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_broadcasts_checklist_updated_when_employee_created_event_is_handled(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $broadcaster = Mockery::mock(Broadcaster::class);

        $employeeCache->shouldReceive('upsert')->once();
        $employeeCache->shouldReceive('invalidatePaginatedLists')->once();
        $checklistCache->shouldReceive('invalidate')->once();
        $broadcaster->shouldReceive('broadcastEmployeeCreated')->once();
        $broadcaster->shouldReceive('broadcastChecklistUpdated')->once()->with('USA', Mockery::type('array'));

        (new EmployeeCreatedHandler($employeeCache, $checklistCache, $broadcaster))->handle($this->validEvent());
    }

    public function test_it_throws_when_country_is_missing_in_employee_created_event(): void
    {
        $handler = new EmployeeCreatedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country is missing from EmployeeCreated event.');

        $handler->handle(['data' => ['employee_id' => 10, 'employee' => ['id' => 10]]]);
    }

    public function test_it_throws_when_employee_id_is_missing_in_employee_created_event(): void
    {
        $handler = new EmployeeCreatedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee ID is missing from EmployeeCreated event.');

        $handler->handle(['country' => 'USA', 'data' => ['employee' => ['id' => 10]]]);
    }

    public function test_it_throws_when_employee_snapshot_is_missing_in_employee_created_event(): void
    {
        $handler = new EmployeeCreatedHandler(
            Mockery::mock(EmployeeCacheRepository::class),
            Mockery::mock(ChecklistCacheRepository::class),
            Mockery::mock(Broadcaster::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee snapshot is missing from EmployeeCreated event.');

        $handler->handle(['country' => 'USA', 'data' => ['employee_id' => 10]]);
    }

    private function validEvent(): array
    {
        return [
            'event_id' => 'evt-1',
            'country' => 'USA',
            'data' => [
                'employee_id' => 10,
                'employee' => ['id' => 10],
                'changed_fields' => ['name'],
            ],
        ];
    }
}
