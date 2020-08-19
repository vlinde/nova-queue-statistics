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
    protected $signature = 'check:queued:jobs';

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
     * @return void
     */
    public function handle()
    {
        $records = 0;
        $startsWith = 'redis';
        $exclude = ['default'];

        $queueConfig = collect(config('queue.connections'))
            ->filter(function ($value, $key) use ($startsWith, $exclude) {
                return Str::startsWith($key, $startsWith) &&
                    (
                        !empty($value['connection']) &&
                        !in_array($value['connection'], $exclude)
                    );
            })
            ->groupBy('connection');

        foreach ($queueConfig as $key => $connection) {
            $redis = Redis::connection($key);

            $queues = $connection->pluck('queue');

            foreach ($queues as $queue) {
                $queueKeys = $redis->keys("queues:{$queue}*");

                $records = $this->createOrUpdateQueueStatisticRecord($redis, $key, $queueKeys);
            }
        }

        Log::info(__CLASS__ . ' statistics records count - ' . $records);
    }

    protected function createOrUpdateQueueStatisticRecord($redis, $connection, $queueKeys)
    {
        $records = 0;

        foreach ($queueKeys as $queueKey) {
            $failed = 0;
            $count = 0;

            $type = $redis->type($queueKey);

            $record = QueueStatistic::where([
                'connection' => $connection,
                'queue' => $queueKey
            ])->first();

            if ($type == 'list') {
                $count = $redis->lLen($queueKey);
            } elseif ($type == 'set') {
                $count = $redis->sCard($queueKey);
            } elseif ($type == 'zset') {
                $count = $redis->zCount(
                    $queueKey,
                    Carbon::now()->subDay()->startOfDay()->timestamp,
                    Carbon::now()->subDay()->endOfDay()->timestamp
                );
            }

            if (!$record) {
                QueueStatistic::create([
                    'connection' => $connection,
                    'queue' => $queueKey,
                    'count' => $count
                ]);
            } else {
                $isTypeListFailed = $type == 'list' && $count >= $record->count;
                $isTypeSetFailed = $type == 'set' && $count>= $record->count;
                $isTypeZSetFailed = $type == 'zset' && $count > 0;

                if ($isTypeListFailed || $isTypeSetFailed || $isTypeZSetFailed) {
                    $failed = 1;
                }

                $record->update([
                    'count' => $count,
                    'failed' => $failed
                ]);
            }

            $records++;
        }

        return $records;
    }

}
