<?php

namespace ProjectSaturnStudios\Xfer\Contracts;

interface BatchDriver
{
    public function process(array $items, bool $use_logging = false): array|bool;
}
