<?php

namespace Vlinde\NovaQueueStatistics\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Vlinde\NovaQueueStatistics\Models\QueueStatistic;

class CheckQueuedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check_queued_jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startsWith = 'redis';
        $exclude = ['default'];

        $queueConfig = collect(config('queue.connections'))->filter(function ($value, $key) use ($startsWith, $exclude) {
            return Str::startsWith($key, $startsWith) && (!empty($value['connection']) && !in_array($value['connection'], $exclude));
        })->groupBy('connection');

        $records = [];

        foreach ($queueConfig as $key => $connection) {
            $redis = Redis::connection($key);

            $queues = $connection->pluck('queue');

            foreach ($queues as $queue) {

                $queueKeys = $redis->keys("queues:{$queue}*");

                foreach ($queueKeys as $queueKey) {
                    $type = $redis->type($queueKey);

                    $records[] = $this->createOrUpdateQueueStatisticRecord($redis, $key, $queueKey, $type);

                }

            }
        }

        Log::info(__CLASS__ . ' statistics records count - ' . count($records));

        return 'done';
    }

    protected function createOrUpdateQueueStatisticRecord($redis, $connection, $queue, $type)
    {

//        QueueStatistic::updateOrCreate(
//            [
//                // create fields
//            ], [
//                // update fields
//            ]
//        );

        $record = QueueStatistic::where([
            'connection' => $connection,
            'queue' => $queue
        ])->first();

        $count = 0;

        if(!$record) {

            if ($type == 'list') {
                $count = $redis->lLen($queue);
            } elseif ($type == 'set') {
                $count = $redis->sCard($queue);
            } elseif ($type == 'zset') {
                $count = $redis->zCount($queue, Carbon::now()->subDay()->startOfDay()->timestamp, Carbon::now()->subDay()->endOfDay()->timestamp);
            }

            return QueueStatistic::create([
                'connection' => $connection,
                'queue' => $queue,
                'count' => $count
            ]);
        }

        $fields = [
            'count' => 0,
        ];

        if ($type == 'list') {
            $fields['count'] = $redis->lLen($queue);

            if($fields['count'] >= $record->count) {
                $fields['failed'] = 1;
            }
        } elseif ($type == 'set') {
            $fields['count'] = $redis->sCard($queue);

            if($fields['count'] >= $record->count) {
                $fields['failed'] = 1;
            }
        } elseif ($type == 'zset') {
            $fields['count'] = $redis->zCount($queue, Carbon::now()->subDay()->startOfDay()->timestamp, Carbon::now()->subDay()->endOfDay()->timestamp);

            if($fields['count'] > 0) {
                $fields['failed'] = 1;
            }
        }

        return tap($record)->update($fields);

    }

}
