<?php

namespace App\Infrastructure\Messaging;

use App\Infrastructure\Messaging\EventRouter;
use App\Infrastructure\Messaging\Exceptions\InvalidEventPayloadException;
use App\Infrastructure\Messaging\Exceptions\NonRetryableConsumerException;
use App\Infrastructure\Messaging\Exceptions\RetryableConsumerException;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseRabbitMQJob;

class RabbitMQJob extends BaseRabbitMQJob
{
    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function fire()
    {
        try {
            $payload = $this->decodePayload();

            $eventRouter = app(EventRouter::class);
            $eventRouter->route($payload);

            Log::info('RabbitMQ message processed successfully', [
                'event_id' => $payload['event_id'] ?? null,
                'event_type' => $payload['event_type'] ?? null,
                'attempt' => $this->attempts(),
            ]);

            $this->delete();
        } catch (NonRetryableConsumerException $e) {
            Log::error('Failed to process RabbitMQ message: non-retryable exception', [
                'error' => $e->getMessage(),
                'exception' => $e::class,
                'raw_body' => $this->getRawBody(),
                'trace' => $e->getTrace(),
            ]);

            $this->fail($e);
        } catch (RetryableConsumerException|Throwable $e) {
            Log::error('Failed to process RabbitMQ message', [
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'error' => $e->getMessage(),
                'exception' => $e::class,
                'raw_body' => $this->getRawBody(),
                'trace' => $e->getTrace(),
            ]);

            throw $e;
        }
    }

    private function decodePayload(): array
    {
        try {
            $payload = json_decode($this->getRawBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new InvalidEventPayloadException('Invalid RabbitMQ payload: unable to decode JSON.', previous: $e);
        }

        if (! \is_array($payload)) {
            throw new InvalidEventPayloadException('Invalid RabbitMQ payload: unable to decode JSON.');
        }

        return $payload;
    }
}
