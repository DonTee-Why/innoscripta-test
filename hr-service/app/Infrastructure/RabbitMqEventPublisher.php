<?php

namespace App\Infrastructure;

use App\Contracts\EventPublisher;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Throwable;

class RabbitMqEventPublisher implements EventPublisher
{
    public function publish(string $routingKey, array $payload): void
    {
        $exchange = config('queue.connections.rabbitmq.options.exchange.name', 'hr.events');
        $exchangeType = config('queue.connections.rabbitmq.options.exchange.type', 'topic');
        $queue = config('queue.connections.rabbitmq.queue', 'hub.events');
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if ($jsonPayload === false) {
            throw new Exception('Failed to JSON encode payload');
        }

        try {
            Queue::connection('rabbitmq')
                ->pushRaw($jsonPayload, $queue, [
                    'exchange' => $exchange,
                    'exchange_type' => $exchangeType,
                    'routing_key' => $routingKey,
                ]);

            Log::info('Published RabbitMQ event', [
                'routing_key' => $routingKey,
                'exchange' => $exchange,
                'event_type' => $payload['event_type'] ?? null,
                'event_id' => $payload['event_id'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to publish RabbitMQ event', [
                'routing_key' => $routingKey,
                'exchange' => $exchange,
                'event_type' => $payload['event_type'] ?? null,
                'event_id' => $payload['event_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
