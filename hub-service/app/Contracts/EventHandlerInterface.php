<?php

namespace App\Contracts;

interface EventHandlerInterface
{
    public function handle(array $payload): void;
}
