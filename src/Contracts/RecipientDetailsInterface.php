<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface RecipientDetailsInterface
{
    public function disk(string $disk): static;
    public function path(string $path): static;
    public function folder(string $folder): static;
    public function getDisk(): ?string;
    public function getPath(): ?string;
    public function getFolder(): ?string;
    public function getFullPath(): ?string;
}
