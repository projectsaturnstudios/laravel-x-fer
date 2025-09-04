<?php

namespace ProjectSaturnStudios\Xfer\Events\Sourced;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use ProjectSaturnStudios\EventSourcing\Events\DataEvent;
use ProjectSaturnStudios\Xfer\DTO\Transfers\TransferResultSuccess;

class FileTransferLogged extends DataEvent
{
    public function __construct(
        public readonly TransferResultSuccess $result,
    ) {}
}
