<?php

namespace ProjectSaturnStudios\Xfer\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ProjectSaturnStudios\Xfer\Contracts\TransferResultInterface;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;
use ProjectSaturnStudios\Xfer\Contracts\FileTransferOrchestratorInterface;

/**
 * @method static FileTransferOrchestratorInterface from(ReadableFileResourceInterface $source)
 * @method static FileTransferOrchestratorInterface to(RecipientDetailsInterface $destination)
 * @method static TransferResultInterface transfer()
 * @see FileTransferOrchestratorInterface
 */
class InitiateTransfer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FileTransferOrchestratorInterface::class;
    }
}
