<?php

namespace ProjectSaturnStudios\Xfer\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ProjectSaturnStudios\Xfer\BatchXfer;

class BatchTransfer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BatchXfer::class;
    }
}
