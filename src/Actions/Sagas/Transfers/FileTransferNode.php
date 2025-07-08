<?php

namespace ProjectSaturnStudios\XFer\Actions\Sagas\Transfers;

use Illuminate\Support\Facades\Event;
use ProjectSaturnStudios\PocketFlow\Node;
use ProjectSaturnStudios\XFer\TransferPackage;
use ProjectSaturnStudios\XFer\Support\Facades\StreamFile;

class FileTransferNode extends Node
{
    public function __construct(protected array $new_files) {
        parent::__construct();

    }
    public function prep(mixed &$shared): mixed
    {

        Event::dispatch('x-fer-transfer-started');
        return $shared;
    }

    public function exec(mixed $prep_res): mixed
    {
        $ct = count($this->new_files);
        /** @var TransferPackage $prep_res */
        foreach(array_values($this->new_files) as $idx => $new_file)
        {
            $idxx = $idx + 1;
            $d_file = basename($new_file);
            Event::dispatch('x-fer-transfer-progress', ['ct' => $ct, 'id' => $idxx, 'file' => $new_file]);
            StreamFile::from($new_file, $prep_res->src_disk)
                ->to("{$prep_res->dest_folder}/{$d_file}", $prep_res->dest_disk);
        }
        return null;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        Event::dispatch('x-fer-transfer-finished');
        return null;
    }
}
