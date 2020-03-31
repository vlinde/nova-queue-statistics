<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::post('/get-queued-jobs', 'Vlinde\NovaQueueStatistics\Http\Controllers\QueueStatisticsController@getQueuedJobs')->name('queue_statistics_get_queued_jobs');
Route::post('/get-failed-jobs', 'Vlinde\NovaQueueStatistics\Http\Controllers\QueueStatisticsController@getFailedJobs')->name('queue_statistics_get_failed_jobs');
Route::post('/get-single-failed-job', 'Vlinde\NovaQueueStatistics\Http\Controllers\QueueStatisticsController@getSingleFailedJob')->name('queue_statistics_get_single_failed_jobs');