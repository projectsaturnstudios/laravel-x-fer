<?php

namespace ProjectSaturnStudios\XFer\Actions\Sagas\Transfers;

use Illuminate\Support\Facades\Event;
use ProjectSaturnStudios\PocketFlow\Node;
use ProjectSaturnStudios\XFer\Enums\FolderState;
use ProjectSaturnStudios\XFer\TransferPackage;

class FolderExistenceNode extends Node
{
    public function __construct(protected string $subject) {
        parent::__construct();
        $this->next(new StartTransferNode, 'start');

    }
    public function prep(mixed &$shared): mixed
    {
        /** @var TransferPackage $shared */
        //$shared->info("Folder Existence Node - check if folder exists in {$this->subject}.");
        return $shared;
    }

    public function exec(mixed $prep_res): mixed
    {
        /** @var TransferPackage $prep_res */
        $folder = $this->subject == 'src' ? $prep_res->src_folder : $prep_res->dest_folder;
        $disk = $this->subject == 'src' ? $prep_res->src_disk : $prep_res->dest_disk;
        $results = list_folder($folder, $disk);

        $prefix = $this->subject == 'src' ? 'from' : 'to';
        if(empty($results) && $this->subject == 'src') {
            $prep_res = $prep_res->setFolderState($prefix, FolderState::NOT_PRESENT);
            $prep_res = $prep_res->addFolderFiles($prefix, []);
            Event::dispatch('x-fer-source-not-ready', ['folder' => $folder, 'disk' => $disk]);

        }
        elseif(empty($results) && $this->subject == 'dest'){
            $prep_res = $prep_res->setFolderState($prefix, FolderState::FOUND);
        }
        else {
            $prep_res = $prep_res->addFolderFiles($prefix, $results);
            $prep_res = $prep_res->setFolderState($prefix, FolderState::FOUND);
        }

        return $prep_res;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared = $exec_res;
        return 'start';
    }
}
