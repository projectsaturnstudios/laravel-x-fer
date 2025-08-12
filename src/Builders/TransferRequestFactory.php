<?php

namespace ProjectSaturnStudios\Xfer\Builders;

use DomainException;
use ProjectSaturnStudios\Xfer\Contracts\RequestFactoryInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;

readonly class TransferRequestFactory implements RequestFactoryInterface
{
    public function __construct(
        protected string $reference_class
    ) {}
    public function make(): TransferRequestInterface
    {
        $results = resolve($this->reference_class);
        if(!$results instanceof TransferRequestInterface) throw new DomainException("Transfer request class must implement TransferRequestInterface");
        return $results;
    }
}
