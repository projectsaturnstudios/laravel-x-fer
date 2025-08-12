<?php

namespace ProjectSaturnStudios\Xfer\DTO;

use Spatie\LaravelData\Data;
use ProjectSaturnStudios\Xfer\Contracts\TransferResultInterface;

class TransferResultSuccess extends Data implements TransferResultInterface
{
    public function __construct() {}

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

