<?php

namespace ProjectSaturnStudios\Xfer\DTO;

use ProjectSaturnStudios\Xfer\Contracts\RecipientDetailsInterface;
use Spatie\LaravelData\Data;

class RecipientDetails extends Data implements RecipientDetailsInterface
{
    public function __construct(
        public readonly ?string $disk = null,
        public readonly ?string $path = null,
        public readonly ?string $folder = null,

    ) {}

    public function disk(string $disk): static
    {
        return new static($disk, $this->path, $this->folder);
    }

    public function path(string $path): static
    {
        return new static($this->disk, $path, $this->folder);
    }

    public function folder(string $folder): static
    {
        return new static($this->disk, $this->path, $folder);
    }

    public function getDisk(): ?string
    {
        return $this->disk;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function getFullPath(): ?string
    {
        if(empty($this->path)) return null;

        $results = "";

        if($this->folder) $results .= "{$this->folder}/";
        $results .= $this->path;

        return $results;
    }


}

