<?php

namespace ProjectSaturnStudios\XFer;

use Spatie\LaravelData\Data;

class FileObject extends Data
{
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
    ) {}
}
