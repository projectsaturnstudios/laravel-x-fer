<?php

namespace ProjectSaturnStudios\Xfer\Actions\FileTransfer;

use ProjectSaturnStudios\Xfer\DTO\Transfers\FileObject;

class TransferFilesAction
{
    /**
     * @param $source
     * @param $destination
     * @return void
     */
    public function handle(FileObject $source, FileObject $destination): bool
    {
        return $destination->write($source->contents());
    }
}
