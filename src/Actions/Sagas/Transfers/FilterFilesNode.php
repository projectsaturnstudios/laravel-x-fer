<?php

namespace ProjectSaturnStudios\XFer\Actions\Sagas\Transfers;

use Illuminate\Support\Facades\Event;
use ProjectSaturnStudios\PocketFlow\Node;
use ProjectSaturnStudios\XFer\Enums\FolderState;
use ProjectSaturnStudios\XFer\TransferPackage;

class FilterFilesNode extends Node
{
    public function __construct() {
        parent::__construct();

    }
    public function prep(mixed &$shared): mixed
    {
        /** @var TransferPackage $shared */
        $shared->info("FilterFilesNode - Filtering files down to just unimported ones in the source folder");
        return $shared;
    }

    public function exec(mixed $prep_res): mixed
    {
        /** @var TransferPackage $prep_res */
        $src_files = $prep_res->getFolderFiles('from');
        $dest_files = $prep_res->getFolderFiles('to');
        $dest_folder = $prep_res->dest_folder;
        return array_filter($src_files, function($src_file) use ($dest_files, $dest_folder) {
            // Check if the file exists in the destination folder
            $file_name = basename($src_file);
            return !in_array("{$dest_folder}/{$file_name}", $dest_files);
        });
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        if(count($exec_res) > 0)
        {
            $ct = count($exec_res);
            Event::dispatch('x-fer-data-filtered', ['count' => $ct]);
            $this->next(new FileTransferNode($exec_res), 'transfer');
            return 'transfer';
        }

        $shared->info("FilterFilesNode -No files to transfer. All Done!");
        return null;
    }
}
