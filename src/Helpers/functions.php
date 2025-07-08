<?php

use ProjectSaturnStudios\XFer\XFer;
use ProjectSaturnStudios\XFer\Support\Facades\StreamFile;
use ProjectSaturnStudios\XFer\Actions\Contents\ListDiskFolderContents;

if(!function_exists('stream_from'))
{
    function stream_from(string $source_file, ?string $source_disk = null): Xfer
    {
        return StreamFile::from($source_file, $source_disk);
    }
}

if(!function_exists('list_folder'))
{
    /**
     * Lists all files in a specified folder on a storage disk.
     *
     * @param string $folder The folder path to list
     * @param string $disk   The storage disk to use (default: 'import-file-s3')
     *
     * @return array Array of file paths in the folder
     */
    function list_folder(string $folder, string $disk = 's3') : array
    {
        return (new ListDiskFolderContents)->handle($folder, $disk);
    }
}
