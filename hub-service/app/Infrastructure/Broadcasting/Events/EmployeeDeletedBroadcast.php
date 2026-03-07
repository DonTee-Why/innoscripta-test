<?php

namespace App\Infrastructure\Broadcasting\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeDeletedBroadcast implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $country,
        public readonly int $employeeId,
        public readonly array $payload,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("country.{$this->country}.employees"),
            new Channel("country.{$this->country}.employee.{$this->employeeId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'employee.deleted';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}