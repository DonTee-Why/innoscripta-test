<?php

namespace App\Infrastructure\Messaging;

use App\Infrastructure\Messaging\EventRouter;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseRabbitMQJob;

class RabbitMQJob extends BaseRabbitMQJob
{
    public function fire()
    {
        try {
            $payload = json_decode($this->getRawBody(), true);
            Log::info($payload);

            if (! \is_array($payload) || $payload === null) {
                throw new RuntimeException('Invalid RabbitMQ payload: unable to decode JSON.');
            }

            $eventRouter = app(EventRouter::class);
            $eventRouter->route($payload);

            $this->delete();
        } catch (Throwable $e) {
            Log::error('Failed to process RabbitMQ message', [
                'message' => $e->getMessage(),
                'raw_body' => $this->getRawBody(),
                'trace' => $e->getTrace(),
            ]);

            $this->fail($e);
        }
    }
}
