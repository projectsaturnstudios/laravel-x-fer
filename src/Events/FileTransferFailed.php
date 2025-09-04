<?php

namespace ProjectSaturnStudios\Xfer\Events;

use DateTime;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;

class FileTransferFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $transfer_id,
        public readonly FileObject $source,
        public readonly FileObject $destination,
        public readonly ?DateTime $time_started
    )
    {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
