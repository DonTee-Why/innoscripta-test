<?php

namespace App\Contracts;

interface EventPublisher
{
    /**
     * Publish a domain event to the event bus.
     *
     * @param string $routingKey
     * @param array<string, mixed> $payload
     */
    public function publish(string $routingKey, array $payload): void;
}
