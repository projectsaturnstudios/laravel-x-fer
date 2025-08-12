<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface RequestFactoryInterface
{
    public function make(): TransferRequestInterface;
}
