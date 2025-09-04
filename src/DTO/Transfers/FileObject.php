<?php

namespace ProjectSaturnStudios\Xfer\DTO\Transfers;

use Spatie\LaravelData\Data;
use Illuminate\Support\Facades\Storage;
use ProjectSaturnStudios\Xfer\Contracts\FileObject as FileObjectContract;

class FileObject extends Data implements FileObjectContract
{
    public function __construct(
        public readonly string $disk,
        public readonly string $filepath,
    ) {}

    /**
     * @return resource|null
     */
    public function contents()
    {
        return Storage::disk($this->disk)->readStream($this->filepath);
    }

    /**
     * @param $contents
     * @return bool
     */
    public function write($contents): bool
    {
        return Storage::disk($this->disk)->writeStream($this->filepath, $contents);
    }

    public function disk(): ?string
    {
        return $this->disk;
    }

    public function path(): ?string
    {
        $pathParts = pathinfo($this->filepath);
        return $pathParts['basename'];
    }

    public function folder(): ?string
    {
        $pathParts = pathinfo($this->filepath);
        return $pathParts['dirname'];//=== '.' ? null : $pathParts['dirname'];
    }
}
