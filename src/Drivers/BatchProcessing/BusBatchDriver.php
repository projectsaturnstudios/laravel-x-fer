<?php

namespace ProjectSaturnStudios\Xfer\Drivers\BatchProcessing;

use Illuminate\Support\Facades\Bus;
use ProjectSaturnStudios\Xfer\Contracts\FileObject;
use ProjectSaturnStudios\Xfer\XFer as Xfer;

class BusBatchDriver extends BatchProcessingDriver
{
    public function process(array $items, bool $use_logging = false): bool
    {
        $batch_q = [];
        $logic = function (array $item) use($use_logging) {
            /** @var FileObject $source */
            /** @var FileObject $destination */
            [$source, $destination] = $item;

            $task = (new Xfer())->from($source->disk(), $source->folder(), $source->path())
                ->to($destination->disk(), $destination->folder(), $destination->path());
            if($use_logging) $task = $task->withLogging();

            return $task->transfer();
        };
        foreach($items as $item) {
            $batch_q[] = fn() => $logic($item);
        }

        Bus::batch($batch_q)
            ->name("File Transfer Batch")
            ->finally(fn() => null)
            ->dispatch();

        return true;
    }
}
