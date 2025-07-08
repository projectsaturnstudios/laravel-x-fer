<?php

namespace ProjectSaturnStudios\XFer\Console\Commands;

use Faker\Core\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Laravel\Prompts\Concerns\Events;
use ProjectSaturnStudios\XFer\Actions\Sagas\Transfers\StartTransferNode;
use ProjectSaturnStudios\XFer\FileObject;
use ProjectSaturnStudios\XFer\TransferPackage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('xfer', "Transfer files between two locations")]
class FileExodusCommand extends Command
{
    protected $signature = 'xfer
                            {src-folder : Source Folder name}
                            {dest-folder? : Destination Folder name (Left blank uses the src-folder)}
                            {from? : Override the default source (Optional)}
                            {to? : Override the default destination (Optional)}';

    public function __construct() {
        parent::__construct();
        $this->registerHooks();
    }

    public function handle(): int
    {
        $src_folder = $this->argument('src-folder');
        $dest_folder = $this->argument('dest-folder') ?: $src_folder;
        $src_disk = $this->argument('from') ?: config('file-transfers.source_disk');
        $dest_disk = $this->argument('to') ?: config('file-transfers.destination_disk');

        $this->info("Transferring files from [{$src_disk}]:{$src_folder} to [{$dest_disk}]:{$dest_folder}");

        $shared = (new TransferPackage($src_folder, $src_disk, $dest_folder, $dest_disk))
            ->setInfo(fn(string $info) => $this->show_info($info));

        flow(new StartTransferNode, $shared);
        $this->warn('fin.');
        return 0;
    }

    public function show_info(string $info): void
    {
        $this->info($info);
    }

    protected function importProcessStarted(): void {
        $this->info("importProcessStarted:Start Transfer Node - starting the VALIDATION process - source folder.");
    }
    protected function sourceDirectoryDiscovered(): void {
        $this->info("sourceDirectoryDiscovered:Start Transfer Node - continuing the VALIDATION process - destination folder.");
    }
    protected function sourceDirectoryNotReady(string $folder, string $disk): void {
        $this->warn("sourceDirectoryNotReady: Source folder '{$folder}' on disk '{$disk}' does not exist.");
    }
    protected function destinationDirectoryDiscovered(): void {
        $this->info("destinationDirectoryDiscovered:Start Transfer Node - starting the FILTERING process.");
    }
    protected function sourceDirectoryListingFiltered($ct): void {
        $this->info("sourceDirectoryListingFiltered: FilterFilesNode - $ct files to transfer.");
    }
    protected function transferProcessStarted(): void {
        $this->info("transferProcessStarted: FileTransferNode - Beginning transfer of files.");
    }
    protected function transferProcessProgress($ct, $id, $file): void {
        $this->warn("transferProcessProgress: FileTransferNode - Transferring file {$id} of {$ct}: {$file}");
    }
    protected function transferProcessFinished(): void {
        $this->info("transferProcessFinished: FileTransferNode - All Finished!");
    }


    private function registerHooks(): void
    {
        Event::listen('x-fer-import-started', fn() => $this->importProcessStarted());
        Event::listen('x-fer-source-ready', fn() => $this->sourceDirectoryDiscovered());
        Event::listen('x-fer-dest-ready', fn() => $this->destinationDirectoryDiscovered());
        Event::listen('x-fer-source-not-ready', fn($folder, $disk) => $this->sourceDirectoryNotReady($folder, $disk));
        Event::listen('x-fer-data-filtered', fn($ct) => $this->sourceDirectoryListingFiltered($ct));
        Event::listen('x-fer-transfer-started', fn() => $this->transferProcessStarted());
        Event::listen('x-fer-transfer-progress', fn($ct, $id, $file) => $this->transferProcessProgress($ct, $id, $file));
        Event::listen('x-fer-transfer-finished', fn() => $this->transferProcessFinished());

    }
}
