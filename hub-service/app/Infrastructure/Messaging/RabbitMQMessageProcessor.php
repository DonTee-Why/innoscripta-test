<?php

namespace App\Infrastructure\Messaging;

use App\Infrastructure\Messaging\Exceptions\InvalidEventPayloadException;
use Illuminate\Support\Facades\Log;
use Throwable;

class RabbitMQMessageProcessor
{
    public function __construct(
        private readonly EventRouter $eventRouter
    ) {}

    public function process(string $rawBody, int $attempt): void
    {
        $payload = $this->decodePayload($rawBody);

        $this->eventRouter->route($payload);

        Log::info('RabbitMQ message processed successfully', [
            'event_id' => $payload['event_id'] ?? null,
            'event_type' => $payload['event_type'] ?? null,
            'attempt' => $attempt,
        ]);
    }

    private function decodePayload(string $rawBody): array
    {
        try {
            $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new InvalidEventPayloadException('Invalid RabbitMQ payload: unable to decode JSON.', previous: $e);
        }

        if (! \is_array($payload)) {
            throw new InvalidEventPayloadException('Invalid RabbitMQ payload: unable to decode JSON.');
        }

        return $payload;
    }
}
