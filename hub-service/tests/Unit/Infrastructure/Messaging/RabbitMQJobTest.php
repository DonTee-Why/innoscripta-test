<?php

namespace Tests\Unit\Infrastructure\Messaging;

use App\Infrastructure\Messaging\EventRouter;
use App\Infrastructure\Messaging\Exceptions\InvalidEventPayloadException;
use App\Infrastructure\Messaging\RabbitMQMessageProcessor;
use Mockery\MockInterface;
use Tests\TestCase;

class RabbitMQJobTest extends TestCase
{
    /** @var EventRouter&MockInterface */
    private MockInterface $eventRouterMock;
    private RabbitMQMessageProcessor $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->eventRouterMock = $this->mock(EventRouter::class);
        $this->processor = new RabbitMQMessageProcessor($this->eventRouterMock);
    }

    public function test_it_routes_a_valid_payload_for_processing(): void
    {
        $payload = $this->validPayload('evt-1');

        $this->eventRouterMock->shouldReceive('route')
            ->once()
            ->with($payload);
        $this->processor->process($this->toJson($payload), 1);

        $this->assertTrue(true);
    }

    public function test_it_throws_when_payload_json_is_invalid(): void
    {
        $this->eventRouterMock->shouldNotReceive('route');

        $this->expectException(InvalidEventPayloadException::class);
        $this->expectExceptionMessage('Invalid RabbitMQ payload: unable to decode JSON.');

        $this->processor->process('{invalid json', 1);
    }

    public function test_it_throws_when_payload_json_is_not_an_array(): void
    {
        $this->eventRouterMock->shouldNotReceive('route');

        $this->expectException(InvalidEventPayloadException::class);
        $this->expectExceptionMessage('Invalid RabbitMQ payload: unable to decode JSON.');

        $this->processor->process('null', 1);
    }

    private function validPayload(string $eventId): array
    {
        return [
            'event_id' => $eventId,
            'event_type' => 'EmployeeUpdated',
            'country' => 'USA',
            'data' => [],
        ];
    }

    private function toJson(array $payload): string
    {
        $rawPayload = json_encode($payload);
        $this->assertNotFalse($rawPayload);

        return $rawPayload;
    }
}
