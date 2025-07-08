<?php

namespace ProjectSaturnStudios\XFer;

use Closure;
use Spatie\LaravelData\Data;
use ProjectSaturnStudios\XFer\Enums\FolderState;

class TransferPackage extends Data
{
    protected FolderState $src_folder_state = FolderState::UNDISCOVERED;
    protected FolderState $dest_folder_state = FolderState::UNDISCOVERED;

    protected ?Closure $info_log = null;

    protected array $src_folder_files = [];
    protected array $dest_folder_files = [];

    public function __construct(
        public readonly string $src_folder,
        public readonly string $src_disk,
        public readonly string $dest_folder,
        public readonly string $dest_disk,
    ) {}

    public function setInfo(callable $info): static
    {
        $this->info_log = $info;
        return $this;
    }

    public function info(string $info): void
    {
        $log = $this->info_log;
        $log($info);
    }

    public function folder_resolved(string $disk): bool
    {
        $subject = $disk == 'to' ? $this->dest_folder_state : $this->src_folder_state;
        return $subject != FolderState::UNDISCOVERED;
    }

    public function folder_found(string $disk): bool
    {
        $subject = $disk == 'to' ? $this->dest_folder_state : $this->src_folder_state;
        return $subject == FolderState::FOUND;
    }

    public function folder_not_found(string $disk): bool
    {
        $subject = $disk == 'to' ? $this->dest_folder_state : $this->src_folder_state;
        return $subject == FolderState::NOT_PRESENT;
    }

    public function setFolderState(string $disk, FolderState $state): static
    {
        if ($disk == 'to') {
            $this->dest_folder_state = $state;
        } else {
            $this->src_folder_state = $state;
        }
        return $this;
    }

    public function addFolderFiles(string $disk, array $files): static
    {
        if ($disk == 'to') {
            $this->dest_folder_files = $files;
        } else {
            $this->src_folder_files = $files;
        }
        return $this;
    }

    public function getFolderFiles(string $disk): array
    {
        return $disk == 'to' ? $this->dest_folder_files : $this->src_folder_files;
    }
}
