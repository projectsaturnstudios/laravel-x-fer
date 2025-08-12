<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface TransferActionInterface
{
    public function transfer(TransferRequestInterface $request): TransferResultInterface;
}
