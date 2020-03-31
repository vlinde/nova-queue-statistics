<?php

namespace Vlinde\NovaQueueStatistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Vlinde\NovaQueueStatistics\Models\QueueStatistic;

class QueueStatisticsController extends Controller
{

    public function getQueueStatisticRecords()
    {
        return QueueStatistic::all();
    }

    public function getQueuedJobs(Request $request)
    {
        $queueConfig = $this->getQueueConfig();

        $connections = $queueConfig->keys();

        $failedJobsQuery = DB::table('failed_jobs')->select('id', 'connection', 'queue', 'failed_at');

        // if(!empty($connections)) {
        //     $failedJobsQuery->whereIn('connection', $connections);
        // }

        $failedJobs = $failedJobsQuery->orderBy('failed_at', 'desc')->take(15)->get();

        $queuedJobs = [];

        $queueConfig = $queueConfig->groupBy('connection');

        // foreach($queueConfig as $key => $connection) {
        //     $redis = Redis::connection($key);

        //     $queues = $connection->pluck('queue');

        //     $queuedJobs[$key] = [];

        //     foreach($queues as $queue) {
        //         $redisRecords = $redis->zrangebyscore("queues:{$queue}:delayed", Carbon::now()->subDay()->startOfDay()->timestamp, Carbon::now()->subDay()->endOfDay()->timestamp, ['withscores' => true]);

        //         foreach($redisRecords as $k => $v) {
        //             $queuedJobs[$key][$queue][$v] = $k;
        //         }
        //     }
        // }

        $failedJobs = $failedJobs->groupBy('connection');

        $failedJobsGrouped = $failedJobs->transform(function($item) {
            return $item->groupBy('queue');
        });

        return response()->json([
            'queuedJobs' => $queuedJobs,
            'statistics' => $this->getQueueStatisticRecords(),
            'failedJobs' => $failedJobsGrouped,
            'connections' => $connections,
            'queues' => $queueConfig->each->pluck('queue'),
        ], 200);
    }

    public function getFailedJobs(Request $request)
    {
        $failedJobsQuery = DB::table('failed_jobs')->select('id', 'connection', 'queue', 'failed_at');

        if(!empty($request->params['connection'])) {
            $failedJobsQuery->where('connection', $request->params['connection']);
        }

        if(!empty($request->params['queue'])) {
            $failedJobsQuery->where('queue', $request->params['queue']);
        }

        $failedJobs = $failedJobsQuery->orderBy('failed_at', 'desc')->take(15)->get();

        $failedJobs = $failedJobs->groupBy('connection');

        $failedJobs = $failedJobs->transform(function($item) {
            return $item->groupBy('queue');
        });

        return response()->json([
            'failedJobs' => $failedJobs,
        ], 200);

    }

    public function getSingleFailedJob(Request $request)
    {
        $job = DB::table('failed_jobs')->select('id', 'exception')->where('id', $request->job)->first();

        return response()->json([
            'job' => $job,
        ], 200);

    }

    public function getQueueConfig($startsWith = 'redis', $exclude = ['default'])
    {
        return collect(config('queue.connections'))->filter(function ($value, $key) use ($startsWith, $exclude) {
            return Str::startsWith($key, $startsWith) && (!empty($value['connection']) && !in_array($value['connection'], $exclude));
        });
    }

}