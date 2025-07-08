<?php

namespace ProjectSaturnStudios\XFer\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void from(string $source_file, ?string $source_disk = null)
 * @method static void to(string $source_file, ?string $source_disk = null)
 */
class StreamFile extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'stream-file';
    }
}
