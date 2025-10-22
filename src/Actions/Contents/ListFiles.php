<?php

namespace ProjectSaturnStudios\Xfer\Actions\Contents;

use Lorisleiva\Actions\Concerns\AsAction;

class ListFiles
{
    use AsAction;

    public function handle(string $authority, string $source): array
    {
        $results = [];

        if($folder_association = transfer_association($authority))
        {
            if($sftp_folder = $folder_association[$source] ?? null)
                $results = ListDiskFolderContents::run($sftp_folder, $source);
        }

        return $results;
    }
}
