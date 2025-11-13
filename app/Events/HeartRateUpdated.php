<?php

namespace App\Events;

use App\Models\HeartRateData; // Add this line
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HeartRateUpdated implements ShouldBroadcast // Implement ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $heartRateData; // Public property to hold the HeartRateData

    /**
     * Create a new event instance.
     */
    public function __construct(HeartRateData $heartRateData) // Constructor to accept HeartRateData
    {
        $this->heartRateData = $heartRateData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to a private channel specific to the user
        return [
            new PrivateChannel('heart-rate.' . $this->heartRateData->user_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'heart-rate-updated';
    }
}
