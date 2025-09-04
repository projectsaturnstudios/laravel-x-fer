<?php

namespace ProjectSaturnStudios\Xfer\DTO\Transfers;

use DateTime;
use Spatie\LaravelData\Data;
use ProjectSaturnStudios\Xfer\Contracts\TransferResult as TransferResultContract;

class TransferResultSuccess extends Data implements TransferResultContract
{
    public function __construct(
        public readonly string $transfer_id,
        public readonly FileObject $source,
        public readonly FileObject $destination,
        public readonly DateTime $time_started,
        public readonly DateTime $time_finished,
    ) {}

    public function success(): bool
    {
        return true;
    }

    public function exception(): ?\Exception
    {
        return null;
    }

    public function message(): string
    {
        return 'Transfer completed successfully';
    }
}

