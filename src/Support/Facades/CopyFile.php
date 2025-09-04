<?php

namespace ProjectSaturnStudios\Xfer\Support\Facades;

use ProjectSaturnStudios\Xfer\XFer as Xfer;
use Illuminate\Support\Facades\Facade;

class CopyFile extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Xfer::class;
    }
}
