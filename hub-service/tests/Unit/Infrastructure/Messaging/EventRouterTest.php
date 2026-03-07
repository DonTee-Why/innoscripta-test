<?php

namespace Tests\Unit\Infrastructure\Messaging;

use App\EventHandlers\EmployeeCreatedHandler;
use App\EventHandlers\EmployeeDeletedHandler;
use App\EventHandlers\EmployeeUpdatedHandler;
use App\Infrastructure\Messaging\EventRouter;
use App\Infrastructure\Messaging\Exceptions\InvalidEventPayloadException;
use App\Infrastructure\Messaging\Exceptions\UnsupportedEventTypeException;
use Mockery\MockInterface;
use Tests\TestCase;

class EventRouterTest extends TestCase
{
    private EventRouter $router;
    /** @var EmployeeCreatedHandler&MockInterface */
    private MockInterface $createdHandler;
    /** @var EmployeeUpdatedHandler&MockInterface */
    private MockInterface $updatedHandler;
    /** @var EmployeeDeletedHandler&MockInterface */
    private MockInterface $deletedHandler;

    public function setUp(): void
    {
        parent::setUp();

        $this->router = new EventRouter();
        $this->createdHandler = $this->mock(EmployeeCreatedHandler::class);
        $this->updatedHandler = $this->mock(EmployeeUpdatedHandler::class);
        $this->deletedHandler = $this->mock(EmployeeDeletedHandler::class);
    }

    public function test_it_routes_employee_created_event_to_employee_created_handler(): void
    {
        $event = $this->event('EmployeeCreated', 'evt-001', 'USA', 1);

        $this->createdHandler->shouldReceive('handle')->once()->with($event);
        $this->updatedHandler->shouldNotReceive('handle');
        $this->deletedHandler->shouldNotReceive('handle');

        $this->router->route($event);
    }

    public function test_it_routes_employee_updated_event_to_employee_updated_handler(): void
    {
        $event = $this->event('EmployeeUpdated', 'evt-002', 'USA', 1);

        $this->updatedHandler->shouldReceive('handle')->once()->with($event);
        $this->createdHandler->shouldNotReceive('handle');
        $this->deletedHandler->shouldNotReceive('handle');

        $this->router->route($event);
    }

    public function test_it_routes_employee_deleted_event_to_employee_deleted_handler(): void
    {
        $event = $this->event('EmployeeDeleted', 'evt-003', 'Germany', 2);

        $this->deletedHandler->shouldReceive('handle')->once()->with($event);
        $this->createdHandler->shouldNotReceive('handle');
        $this->updatedHandler->shouldNotReceive('handle');

        $this->router->route($event);
    }

    public function test_it_throws_when_event_type_is_missing(): void
    {
        $this->expectException(InvalidEventPayloadException::class);
        $this->expectExceptionMessage('Event type is missing from payload.');

        $this->router->route([
            'event_id' => 'evt-004',
            'country' => 'USA',
            'data' => [],
        ]);
    }

    public function test_it_throws_when_event_type_is_unsupported(): void
    {
        $this->expectException(UnsupportedEventTypeException::class);
        $this->expectExceptionMessage('Unsupported event type [EmployeeArchived]');

        $this->router->route([
            'event_type' => 'EmployeeArchived',
            'event_id' => 'evt-005',
            'country' => 'USA',
            'data' => [],
        ]);
    }

    private function event(string $eventType, string $eventId, string $country, int $employeeId): array
    {
        return [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'country' => $country,
            'data' => [
                'employee_id' => $employeeId,
                'employee' => ['id' => $employeeId],
            ],
        ];
    }
}
