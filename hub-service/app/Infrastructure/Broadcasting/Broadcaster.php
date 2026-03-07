<?php

namespace App\Infrastructure\Broadcasting;

use App\Infrastructure\Broadcasting\Events\EmployeeCreatedBroadcast;
use App\Infrastructure\Broadcasting\Events\EmployeeUpdatedBroadcast;
use App\Infrastructure\Broadcasting\Events\EmployeeDeletedBroadcast;
use App\Infrastructure\Broadcasting\Events\ChecklistUpdatedBroadcast;

class Broadcaster
{
    public function broadcastEmployeeCreated(string $country, int $employeeId, array $payload): void
    {
        broadcast(new EmployeeCreatedBroadcast(
            country: $country,
            employeeId: $employeeId,
            payload: $payload,
        ));
    }

    public function broadcastEmployeeUpdated(string $country, int $employeeId, array $payload): void
    {
        broadcast(new EmployeeUpdatedBroadcast(
            country: $country,
            employeeId: $employeeId,
            payload: $payload,
        ));
    }

    public function broadcastEmployeeDeleted(string $country, int $employeeId, array $payload): void
    {
        broadcast(new EmployeeDeletedBroadcast(
            country: $country,
            employeeId: $employeeId,
            payload: $payload,
        ));
    }

    public function broadcastChecklistUpdated(string $country, array $payload): void
    {
        broadcast(new ChecklistUpdatedBroadcast(
            country: $country,
            payload: $payload,
        ));
    }
}