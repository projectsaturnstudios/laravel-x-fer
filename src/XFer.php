<?php

namespace ProjectSaturnStudios\XFer;

use ProjectSaturnStudios\XFer\Actions\Transfers\SourceToDestinationService;

class XFer
{
    protected ?FileObject $from;

    public function from(string $source_file, ?string $source_disk = null): static
    {
        $source_disk = $source_disk ?? config('file-transfers.source_disk', 'stfp');
        $this->from = new FileObject($source_disk, $source_file);
        return $this;
    }

    public function to(string $source_file, ?string $source_disk = null): void
    {
        $source_disk = $source_disk ?? config('file-transfers.destination_disk', 's3');
        $to = new FileObject($source_disk, $source_file);
        (new SourceToDestinationService)->handle($to, $this->from);
    }

    public static function boot(): void
    {
        app()->instance('stream-file', new static());
    }
}
