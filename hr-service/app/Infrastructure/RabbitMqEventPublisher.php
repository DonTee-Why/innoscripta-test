<?php

namespace App\Infrastructure;

use App\Contracts\EventPublisher;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class RabbitMqEventPublisher implements EventPublisher
{
    public function publish(string $routingKey, array $payload): void
    {
        $exchange = config('queue.connections.rabbitmq.options.exchange.name', 'hr.events');
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if ($jsonPayload === false) {
            throw new Exception('Failed to JSON encode payload');
        }

        try {
            /** @var RabbitMQQueue $connection */
            $connection = Queue::connection('rabbitmq');

            $message = new AMQPMessage($jsonPayload, [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $connection->getChannel()->basic_publish($message, $exchange, $routingKey);

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
