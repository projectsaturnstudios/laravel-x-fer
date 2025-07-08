<?php

namespace ProjectSaturnStudios\XFer\Actions\Sagas\Transfers;

use Illuminate\Support\Facades\Event;
use ProjectSaturnStudios\PocketFlow\Node;
use ProjectSaturnStudios\XFer\TransferPackage;

class StartTransferNode extends Node
{
    public function prep(mixed &$shared): mixed
    {

        return $shared;
    }

    public function exec(mixed $prep_res): mixed
    {
        /** @var TransferPackage $prep_res */
        if(!$prep_res->folder_resolved('from'))
        {
            Event::dispatch('x-fer-import-started');
            $this->next(new FolderExistenceNode('src'), 'validate-source');
            return 'validate-source';
            //
        }
        elseif($prep_res->folder_not_found('from')) return null;

        if(!$prep_res->folder_resolved('to'))
        {
            Event::dispatch('x-fer-source-ready');
            $this->next(new FolderExistenceNode('dest'), 'validate-destination');
            return 'validate-destination';
        }
        elseif($prep_res->folder_not_found('to')) return null;

        if($prep_res->folder_found('from') && $prep_res->folder_found('to'))
        {
            Event::dispatch('x-fer-dest-ready');
            $this->next(new FilterFilesNode(), 'filter');
            return 'filter';
        }

        return null;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {

        return $exec_res;
    }
}
