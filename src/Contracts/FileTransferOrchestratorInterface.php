<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface FileTransferOrchestratorInterface
{
    public function to(RecipientDetailsInterface $destination): static;

    public function from(ReadableFileResourceInterface $source): static;

    public function transfer(): TransferResultInterface;

    public function ready(): bool;
}
