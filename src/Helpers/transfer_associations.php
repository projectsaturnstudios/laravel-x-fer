<?php

if(!function_exists('transfer_association')) {
    /**
     * Get the folder associations for a given authority.
     *
     * @param string $authority
     * @return ?array
     */
    function transfer_association(string $authority): ?array
    {
        return config("file-transfers.folder_associations.{$authority}", null);
    }
}
