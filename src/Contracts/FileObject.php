<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface FileObject
{
    /**
     * Get the file contents as a stream resource
     *
     * @return resource|null
     */
    public function contents();

    /**
     * Write contents to the file from a stream
     *
     * @param resource $contents
     * @return bool
     */
    public function write($contents): bool;

    public function disk(): ?string;
    public function path(): ?string;
    public function folder(): ?string;
}
