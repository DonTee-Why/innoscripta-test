<?php

namespace App\Infrastructure\Broadcasting\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChecklistUpdatedBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $country,
        public readonly array $payload,
    ) {
        $this->country = strtolower($this->country);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("country.{$this->country}.checklists"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'checklist.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}