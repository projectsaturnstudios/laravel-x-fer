<?php

namespace ProjectSaturnStudios\Xfer\Support\Facades;

use Illuminate\Support\Facades\Facade;
use ProjectSaturnStudios\Xfer\Contracts\BatchDriver;
use ProjectSaturnStudios\Xfer\Managers\BatchProcessingManager;

/**
 * @method static BatchDriver driver(?string $name = null)
 * @see BatchProcessingManager
 */
class BatchManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BatchProcessingManager::class;
    }
}
