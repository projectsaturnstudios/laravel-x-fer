<?php

namespace ProjectSaturnStudios\XFer\Actions\Contents;

use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists files in a specified folder on a storage disk.
 *
 * This action retrieves a list of all files in a given folder path
 * from a specified storage disk.
 *
 * @package RDMIntegrations\ToolBox\Actions\Utilities\Storage
 */
class ListDiskFolderContents
{
    use AsAction;

    /**
     * List all files in a folder on a storage disk.
     *
     * @param string $folder The folder path to list
     * @param string $disk   The storage disk to use (default: 's3')
     *
     * @return array Array of file paths in the folder
     */
    public function handle(string $folder, string $disk = 's3')
    {
        return Storage::disk($disk)->files($folder);
    }
}
