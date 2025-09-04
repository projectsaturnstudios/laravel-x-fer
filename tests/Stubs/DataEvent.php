<?php

namespace ProjectSaturnStudios\EventSourcing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Stub for testing - represents the DataEvent from the event sourcing package
 */
abstract class DataEvent
{
    use Dispatchable, SerializesModels;
}


