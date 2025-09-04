<?php

namespace ProjectSaturnStudios\Xfer\Projections;

use Illuminate\Database\Eloquent\Model;

class FileTransferLog extends Model
{
    /**
     * @var mixed|string
     */
    protected $table = 'file_transfer_logs';

    protected $fillable = [
        'transfer_id', 'source_disk', 'source_filepath', 'destination_disk', 'destination_filepath', 'time_started', 'time_finished'
    ];
}
