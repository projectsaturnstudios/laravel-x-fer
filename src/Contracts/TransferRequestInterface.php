<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface TransferRequestInterface
{
    public function ready(): bool;
    public function getDestination(): ?RecipientDetailsInterface;
    public function source(ReadableFileResourceInterface $source): static;
    public function destination(RecipientDetailsInterface $destination): static;

    /** @return resource */
    public function getSourceStream();
}
