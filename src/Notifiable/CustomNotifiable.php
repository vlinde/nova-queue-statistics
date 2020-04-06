<?php

namespace Vlinde\NovaQueueStatistics\Notifiable;

use Vlinde\NovaQueueStatistics\Classes\RoutesThrottledNotifications;

class CustomNotifiable
{
    use RoutesThrottledNotifications;

    public function routeNotificationForMail(): string
    {
        return config('queue_statistics.mail.to');
    }

    public function routeNotificationForSlack(): string
    {
        return config('queue_statistics.slack.webhook_url');
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return 1;
    }
}
