<?php

namespace ProjectSaturnStudios\Xfer\Events;

use DateTime;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;

class FileTransferStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $transfer_id,
        public readonly FileObject $source,
        public readonly FileObject $destination,
        public readonly ?DateTime $time_started
    )
    {}
}
