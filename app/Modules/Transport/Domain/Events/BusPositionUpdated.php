<?php

namespace App\Modules\Transport\Domain\Events;

use App\Modules\Transport\Domain\Models\Bus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Broadcast immediately (no queue worker required) — position updates are
 * only useful in the moment, there is no value in queuing a stale one.
 */
class BusPositionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Bus $bus,
    ) {
    }

    /** One private channel per bus — every parent riding it and the school dashboard share the same channel; routes/channels.php authorizes each principal kind separately. */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("transport.bus.{$this->bus->id}")];
    }

    public function broadcastAs(): string
    {
        return 'bus.position.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'busId' => (string) $this->bus->id,
            'latitude' => (float) $this->bus->current_latitude,
            'longitude' => (float) $this->bus->current_longitude,
            'updatedAt' => $this->bus->position_updated_at?->toIso8601String(),
        ];
    }
}
