<?php

namespace ProjectSaturnStudios\XFer\Exceptions;

use Exception;

class XFerException extends Exception
{
    public static function transferSourceMissing(): static
    {
        return new static("The source file does not exist.");
    }

    public static function transferDestinationMissing(): static
    {
        return new static("The destination file does not exist.");
    }
}
