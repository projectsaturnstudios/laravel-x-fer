<?php

namespace ProjectSaturnStudios\Xfer\Drivers\BatchProcessing;

use Illuminate\Support\Facades\Concurrency;
use ProjectSaturnStudios\Xfer\Contracts\FileObject;
use ProjectSaturnStudios\Xfer\Xfer;

class ConcurrencyBatchDriver extends BatchProcessingDriver
{
    public function process(array $items, bool $use_logging = false): array
    {
        $chain = [];
        
        foreach($items as $item) {
            /** @var FileObject $source */
            /** @var FileObject $destination */
            [$source, $destination] = $item;
            
            // Extract simple data for serialization
            $transferData = [
                'source_disk' => $source->disk(),
                'source_folder' => $source->folder(),
                'source_path' => $source->path(),
                'dest_disk' => $destination->disk(),
                'dest_folder' => $destination->folder(),
                'dest_path' => $destination->path(),
                'use_logging' => $use_logging
            ];
            
            $chain[] = function () use ($transferData) {
                $task = (new Xfer())->from(
                    $transferData['source_disk'], 
                    $transferData['source_folder'], 
                    $transferData['source_path']
                )->to(
                    $transferData['dest_disk'], 
                    $transferData['dest_folder'], 
                    $transferData['dest_path']
                );

                if($transferData['use_logging']) {
                    $task = $task->withLogging();
                }

                return $task->transfer();
            };
        }

        return Concurrency::run($chain);
    }
}
