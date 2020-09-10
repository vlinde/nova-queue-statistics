<?php

namespace Vlinde\NovaQueueStatistics\Observers;

use Vlinde\NovaQueueStatistics\Models\QueueStatistic;

class QueueStatisticObserver
{
    /**
     * Handle the "created" event.
     *
     * @param QueueStatistic $queueStatistic
     * @return void
     */
    public function created(QueueStatistic $queueStatistic)
    {
        //
    }

    /**
     * Handle "updated" event.
     *
     * @param QueueStatistic $queueStatistic
     * @return void
     */
    public function updated(QueueStatistic $queueStatistic)
    {
        if ($queueStatistic->failed) {
            $notifiable = app(config('queue_statistics.notifiable'));
            $notification = app(config('queue_statistics.statistic.notification'))
                ->setStatistic($queueStatistic);

            $notifiable->notify($notification);
        }
    }

    /**
     * Handle the "deleted" event.
     *
     * @param QueueStatistic $queueStatistic
     * @return void
     */
    public function deleting(QueueStatistic $queueStatistic)
    {
        //
    }

    /**
     * Handle the "restored" event.
     *
     * @param QueueStatistic $queueStatistic
     * @return void
     */
    public function restored(QueueStatistic $queueStatistic)
    {
        //
    }

    /**
     * Handle the "force deleted" event.
     *
     * @param QueueStatistic $queueStatistic
     * @return void
     */
    public function forceDeleted(QueueStatistic $queueStatistic)
    {
        //
    }
}
