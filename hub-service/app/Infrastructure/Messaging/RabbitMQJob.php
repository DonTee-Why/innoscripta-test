<?php

namespace App\Infrastructure\Messaging;

use App\Infrastructure\Messaging\Exceptions\NonRetryableConsumerException;
use App\Infrastructure\Messaging\Exceptions\RetryableConsumerException;
use Illuminate\Support\Facades\Log;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseRabbitMQJob;

class RabbitMQJob extends BaseRabbitMQJob
{
    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function fire()
    {
        try {
            $processor = app(RabbitMQMessageProcessor::class);
            $processor->process($this->getRawBody(), $this->attempts());

            $this->delete();
        } catch (NonRetryableConsumerException $e) {
            Log::error('Failed to process RabbitMQ message: non-retryable exception', [
                'error' => $e->getMessage(),
                'exception' => $e::class,
                'raw_body' => $this->getRawBody(),
                'trace' => $e->getTrace(),
            ]);

            $this->fail($e);
        } catch (Throwable $e) {
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

    public function failed($e): void
    {
        Log::error('RabbitMQ message permanently failed', [
            'error' => $e->getMessage(),
            'exception' => $e::class,
            'raw_body' => $this->getRawBody(),
            'trace' => $e->getTrace(),
        ]);

        parent::failed($e);
    }
}
