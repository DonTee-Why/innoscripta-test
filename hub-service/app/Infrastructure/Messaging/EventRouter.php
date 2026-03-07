<?php

namespace App\Infrastructure\Messaging;

use App\Contracts\EventHandlerInterface;
use App\EventHandlers\EmployeeCreatedHandler;
use App\EventHandlers\EmployeeDeletedHandler;
use App\EventHandlers\EmployeeUpdatedHandler;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EventRouter
{
    public function route(array $payload): void
    {
        $eventType = $payload['event_type'] ?? null;

        if (! \is_string($eventType) || $eventType === '') {
            throw new InvalidArgumentException('Event type is missing from payload.');
        }

        Log::info('Routing incoming event', [
            'event_type' => $eventType,
            'event_id' => $payload['event_id'] ?? null,
            'country' => $payload['country'] ?? null,
        ]);

        $handler = $this->getHandler($eventType);
        $handler->handle($payload);
    }

    private function getHandler(string $eventType): EventHandlerInterface
    {
        return match ($eventType) {
            'EmployeeCreated' => app(EmployeeCreatedHandler::class),
            'EmployeeUpdated' => app(EmployeeUpdatedHandler::class),
            'EmployeeDeleted' => app(EmployeeDeletedHandler::class),
            default => throw new InvalidArgumentException("Unsupported event type [{$eventType}]"),
        };
    }
}