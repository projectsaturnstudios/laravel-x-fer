<?php

namespace ProjectSaturnStudios\Xfer\DTO;

use Exception;
use Spatie\LaravelData\Data;
use ProjectSaturnStudios\Xfer\Contracts\TransferResultInterface;

class TransferResultFailure extends Data implements TransferResultInterface
{
    public function __construct(
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
