<?php

namespace ProjectSaturnStudios\Xfer\DTO\Transfers;

use Exception;
use Spatie\LaravelData\Data;
use ProjectSaturnStudios\Xfer\Contracts\TransferResult as TransferResultContract;

class TransferResultFailure extends Data implements TransferResultContract
{
    public function __construct(
        public readonly string $transfer_id,
        public readonly string $message = 'Transfer failed',
        public readonly ?Exception $exception = null,
    ) {}

    public function success(): bool
    {
        return false;
    }

    public function  exception(): ?Exception
    {
        return $this->exception;
    }

    public function message(): string
    {
        return $this->message;
    }
}
