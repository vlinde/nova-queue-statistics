<?php

namespace Vlinde\NovaQueueStatistics\Models;

use Illuminate\Database\Eloquent\Model;

class QueueStatistic extends Model
{
    protected $table = 'queue_statistics';

    public $timestamps = false;

    protected $fillable = [
        'connection',
        'queue',
        'count',
        'failed'
    ];

}
