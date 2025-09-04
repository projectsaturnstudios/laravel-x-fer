<?php

namespace ProjectSaturnStudios\Xfer;

use ProjectSaturnStudios\Xfer\Contracts\FileObject;
use ProjectSaturnStudios\Xfer\Contracts\BatchDriver;
use ProjectSaturnStudios\Xfer\Support\Facades\BatchManager;

class BatchXfer
{
    public function __construct(
        protected readonly ?BatchDriver $batch_driver = null,
        protected readonly array $transfers = [],
        protected bool $use_logging = false
    ) {}

    public function driver(?string $name = null): static
    {
        $driver = BatchManager::driver($name);
        return new static($driver, $this->transfers, $this->use_logging);
    }

    public function addTransfer(FileObject $source, FileObject $destination): static
    {
        $new_transfer = $this->transfers;
        $new_transfer[] = [$source, $destination];
        return new static($this->batch_driver, $new_transfer, $this->use_logging);
    }

    public function addTransfers(array $transfers): static
    {
        $new_transfer = $this->transfers;
        foreach($transfers as $transfer)
        {
            if(is_array($transfer) && count($transfer) === 2 && $transfer[0] instanceof FileObject && $transfer[1] instanceof FileObject)
            {
                $new_transfer[] = [$transfer[0], $transfer[1]];
            }
        }
        return new static($this->batch_driver, $new_transfer, $this->use_logging);
    }

    public function withLogging(): static
    {
        return new static($this->batch_driver, $this->transfers, true);
    }

    public function dispatch(): array|bool
    {
        return $this->batch_driver->process($this->transfers, $this->use_logging);
    }
}

