<?php

namespace ProjectSaturnStudios\Xfer\Actions;

use Exception;
use DomainException;
use Illuminate\Support\Facades\Event;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use ProjectSaturnStudios\Xfer\DTO\TransferResultFailure;
use ProjectSaturnStudios\Xfer\DTO\TransferResultSuccess;
use ProjectSaturnStudios\Xfer\Contracts\TransferActionInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferResultInterface;
use ProjectSaturnStudios\Xfer\Contracts\TransferRequestInterface;

readonly class TransferAction implements TransferActionInterface
{
    public function __construct(
        protected Storage $storage,
        protected Events $events,
    ) {}
    public function transfer(TransferRequestInterface $request): TransferResultInterface
    {
        try {
            if(!$this->send($request)) return new TransferResultFailure();
        }
        catch (Exception $e) {
            return new TransferResultFailure($e->getMessage(), $e);
        }

        return new TransferResultSuccess();
    }

    private function send(TransferRequestInterface $request): bool
    {
        $this->events->dispatch(config('file-transfers.events.started', 'file_transfer.started'), [$request]);
        $source_stream = $request->getSourceStream();
        $file_path = $request->getDestination()->getFullPath();
        if(empty($file_path)) throw new DomainException("Destination file path is empty.");
        try {
            $results = $this->storage
                ->disk($request->getDestination()->disk)
                ->writeStream($file_path, $source_stream);
        }
        catch (Exception $e) {
            $this->events->dispatch(config('file-transfers.events.failed', 'file_transfer.failed'), [$request, $e]);
            $results = false;
        }
        finally {
            if(is_resource($source_stream)) {
                fclose($source_stream);
            }
            if($results) $this->events->dispatch(config('file-transfers.events.finished', 'file_transfer.finished'), [$request]);
        }

        return $results;
    }
}
