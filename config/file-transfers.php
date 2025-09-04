<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Transfer Batch Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the batch driver that will be used to process file
    | transfers. You may set this to any of the drivers provided by the
    | package or create your own custom driver that implements the
    | ProjectSaturnStudios\Xfer\Contracts\BatchDriver interface.
    |
    | Supported: "sync", "bus", "concurrency"
    |
    */

    'batch_processing' => [
        'driver' => env('XFER_BATCH_DRIVER', 'sync'),
        'drivers' => [
            'sync'          => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\SyncBatchDriver::class,
            'bus'           => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\BusBatchDriver::class,
            'chain'         => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ChainBatchDriver::class,
            'concurrency'   => ProjectSaturnStudios\Xfer\Drivers\BatchProcessing\ConcurrencyBatchDriver::class,
        ],
    ],
];
