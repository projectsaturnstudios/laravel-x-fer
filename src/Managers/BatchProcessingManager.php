<?php

namespace ProjectSaturnStudios\Xfer\Managers;

use Illuminate\Support\Manager;
use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\BusBatchDriver;
use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\BatchProcessingDriver;
use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ChainBatchDriver;
use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ConcurrencyBatchDriver;
use ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\SyncBatchDriver;

class BatchProcessingManager extends Manager
{
    public function createConcurrencyDriver(): BatchProcessingDriver
    {
        return new ConcurrencyBatchDriver();
    }

    public function createBusDriver(): BatchProcessingDriver
    {
        return new BusBatchDriver();
    }

    public function createChainDriver(): BatchProcessingDriver
    {
        return new ChainBatchDriver();
    }

    public function createSyncDriver(): BatchProcessingDriver
    {
        return new SyncBatchDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('file-transfers.batch_processing.default', 'sync');
    }

    public static function boot(): void
    {
        app()->singleton(static::class, fn($app) => new static($app));
    }
}
