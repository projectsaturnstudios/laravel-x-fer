<?php

namespace ProjectSaturnStudios\Xfer;

use Exception;
use ProjectSaturnStudios\Xfer\Contracts\TransferResult;
use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;
use ProjectSaturnStudios\Xfer\Exceptions\XFerException;
use ProjectSaturnStudios\Xfer\Events\FileTransferFailed;
use ProjectSaturnStudios\Xfer\Events\FileTransferStarted;
use ProjectSaturnStudios\Xfer\Events\FileTransferFinished;
use ProjectSaturnStudios\Xfer\Events\Sourced\FileTransferLogged;
use ProjectSaturnStudios\Xfer\DTO\Transfers\TransferResultSuccess;
use ProjectSaturnStudios\Xfer\DTO\Transfers\TransferResultFailure;
use ProjectSaturnStudios\Xfer\Actions\FileTransfer\TransferFilesAction;

readonly class Xfer
{
    public function __construct(
        protected ?FileObject $source = null,
        protected ?FileObject $destination = null,
        protected bool $use_logging = false
    ) {}

    /**
     * @param string $disk
     * @param string $folder
     * @param string $path
     * @return $this
     */
    public function from(string $disk, string $folder, string $path): static
    {
        $file = new FileObject($disk, "{$folder}/{$path}");
        return new static($file, $this->destination, $this->use_logging);
    }

    public function to(string $disk, string $folder, string $path): static
    {
        $file = new FileObject($disk, "{$folder}/{$path}");
        return new static($this->source, $file, $this->use_logging);
    }

    public function withLogging(bool $enabled = true): static
    {
        return new static($this->source, $this->destination, $enabled);
    }

    /**
     * @return bool|TransferResult
     * @throws XFerException
     */
    public function transfer(): bool|TransferResult
    {
        if(empty($this->source)) throw XFerException::transferSourceMissing();
        if(empty($this->destination)) throw XFerException::transferDestinationMissing();
        if($this->use_logging)
        {
            try {
                return $this->send();
            }
            catch (Exception $e) {
                return new TransferResultFailure('no-id', $e->getMessage(), $e);
            }
        }

        return (new TransferFilesAction)->handle($this->source, $this->destination);
    }

    /**
     * @return TransferResult
     * @throws XFerException
     */
    protected function send(): TransferResult
    {
        $now = now();
        $transfer_id = new_uuid4();
        FileTransferStarted::dispatch($transfer_id, $this->source, $this->destination, $now);
        $results = (new static($this->source, $this->destination, false))->transfer();

        if($results)
        {
            $success = new TransferResultSuccess($transfer_id, $this->source, $this->destination, $now, now());

            FileTransferFinished::dispatch($transfer_id, $this->source, $this->destination, $now);
            event(new FileTransferLogged($success));

            return $success;
        }

        FileTransferFailed::dispatch($transfer_id, $this->source, $this->destination, $now, now());
        return new TransferResultFailure($transfer_id);
    }
}
