<?php

namespace ProjectSaturnStudios\Xfer\Drivers\BatchProcessing;

use ProjectSaturnStudios\Xfer\Contracts\BatchDriver as BatchDriverContract;

abstract class BatchProcessingDriver implements BatchDriverContract
{
    abstract public function process(array $items, bool $use_logging = false): array|bool;
}
