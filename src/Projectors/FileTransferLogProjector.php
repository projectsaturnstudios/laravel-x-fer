<?php

namespace ProjectSaturnStudios\Xfer\Projectors;

use ProjectSaturnStudios\Xfer\Events\Sourced\FileTransferLogged;
use ProjectSaturnStudios\Xfer\Projections\FileTransferLog;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class FileTransferLogProjector extends Projector
{
    protected function onFileTransferLogged(FileTransferLogged $event): void
    {
        $model = new FileTransferLog();
        $model->transfer_id = $event->result->transfer_id;
        $model->source_disk = $event->result->source->disk;
        $model->source_filepath = $event->result->source->filepath;
        $model->destination_disk = $event->result->destination->disk;
        $model->destination_filepath = $event->result->destination->filepath;
        $model->time_started = $event->result->time_started;
        $model->time_finished = $event->result->time_finished;
        $model->save();
    }
}
