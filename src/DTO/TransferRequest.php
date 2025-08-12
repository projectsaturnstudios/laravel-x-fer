<?php

namespace ProjectSaturnStudios\Xfer\DTO;

use DomainException;
use Spatie\LaravelData\Data;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;

class TransferRequest extends Data implements TransferRequestInterface
{
    protected string $state = 'uninitialized';

    public function __construct(
        public readonly ?ReadableFileResourceInterface $source = null,
        public readonly ?RecipientDetailsInterface $destination = null
    )
    {
        if($source && $destination) $this->state = 'ready';
        elseif($source || $destination) $this->state = 'pending';
        else $this->state = 'uninitialized';

    }

    public function ready(): bool
    {
        return $this->state == 'ready';
    }

    public function source(ReadableFileResourceInterface $source): static
    {
        return new static($source, $this->destination);
    }

    public function destination(RecipientDetailsInterface $destination): static
    {
        return new static($this->source, $destination);
    }

    public function getDestination(): ?RecipientDetailsInterface
    {
        return $this->destination;
    }

    /**
     * @return resource
     * @throws DomainException
     */
    public function getSourceStream()
    {
        if(empty($this->source)) throw new DomainException("No source has been set for this transfer request.");
        $resource = $this->source->read();
        if(!is_resource($resource)) throw new DomainException("The source did not return a valid stream resource.");
        return $resource;
    }
}
