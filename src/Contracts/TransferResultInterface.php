<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

use Exception;

interface TransferResultInterface
{
    public function success(): bool;

    public function exception(): ?Exception;

    public function message(): string;
}
