<?php

namespace App\Infrastructure\Broadcasting\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeCreatedBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $country,
        public readonly int $employeeId,
        public readonly array $payload,
    ) {
        $this->country = strtolower($this->country);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("country.{$this->country}.employees"),
            new Channel("country.{$this->country}.employee.{$this->employeeId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'employee.created';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
