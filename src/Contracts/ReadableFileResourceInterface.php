<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface ReadableFileResourceInterface
{
    /**
     * @return resource
     */
    public function read();
    public function getDisk(): ?string;
    public function getPath(): ?string;
    public function getFolder(): ?string;
    public function getFullPath(): ?string;
    public function disk(string $disk): static;
    public function path(string $path): static;
    public function folder(string $folder): static;
}
