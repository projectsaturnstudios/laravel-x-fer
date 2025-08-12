<?php

namespace ProjectSaturnStudios\Xfer;

use DomainException;
use ProjectSaturnStudios\Xfer\Contracts\RequestFactoryInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferActionInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferResultInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;
use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;
use ProjectSaturnStudios\Xfer\Contracts\ReadableFileResourceInterface;
use ProjectSaturnStudios\Xfer\Contracts\FileTransferOrchestratorInterface;

class Xfer implements FileTransferOrchestratorInterface
{
    protected string $state = 'uninitialized';

    public function __construct(
       protected readonly RequestFactoryInterface $request_factory,
       protected readonly TransferActionInterface $action,
       protected readonly ?TransferRequestInterface $request = null,
    ) {
        if(empty($this->request)) $this->state = 'uninitialized';
        elseif($this->request->ready()) $this->state = 'ready';
        else $this->state = 'pending';
    }

    public function from(ReadableFileResourceInterface $source): static
    {
        $req = $this->request;
        $req ??= $this->request_factory->make();
        $req = $req->source($source);
        return new static($this->request_factory, $this->action, $req);
    }

    public function to(RecipientDetailsInterface $destination): static
    {
        $req = $this->request;
        $req ??= $this->request_factory->make();
        $req = $req->destination($destination);
        return new static($this->request_factory, $this->action, $req);
    }

    public function transfer(): TransferResultInterface
    {
        if(!$this->request?->ready()) throw new DomainException("Transfer request is not ready for transfer.");
        return $this->action->transfer($this->request);
    }

    public function ready(): bool
    {
        return $this->state == 'ready';
    }
}
