<?php

namespace ProjectSaturnStudios\XFer\Actions\Transfers;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use ProjectSaturnStudios\XFer\FileObject;

class SourceToDestinationService
{
    use AsAction;

    public function handle(FileObject $from, FileObject $to) : void
    {
        Storage::disk($from->disk)
            ->writeStream(
                $from->path,
                Storage::disk($to->disk)->readStream($to->path)
            );
    }
}
