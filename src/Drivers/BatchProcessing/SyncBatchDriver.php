<?php

namespace ProjectSaturnStudios\Xfer\Drivers\BatchProcessing;

use ProjectSaturnStudios\Xfer\Contracts\FileObject;
use ProjectSaturnStudios\Xfer\Support\Facades\CopyFile;

class SyncBatchDriver extends BatchProcessingDriver
{
    public function process(array $items, bool $use_logging = false): array
    {
        return array_map(function (array $item) use($use_logging) {
            /** @var FileObject $source */
            /** @var FileObject $destination */
            [$source, $destination] = $item;

            $task = CopyFile::from($source->disk(), $source->folder(), $source->path())
                ->to($destination->disk(), $destination->folder(), $destination->path());

            if($use_logging) $task = $task->withLogging();

            return $task->transfer();
        }, $items);
    }
}
