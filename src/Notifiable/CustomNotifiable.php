<?php

namespace Vlinde\NovaQueueStatistics\Notifiable;

use Vlinde\NovaQueueStatistics\Classes\RoutesThrottledNotifications;

class CustomNotifiable
{
    use RoutesThrottledNotifications;

    public function routeNotificationForMail(): string
    {
        return config('failed-job-monitor.mail.to');
    }

    public function routeNotificationForSlack(): string
    {
        return config('failed-job-monitor.slack.webhook_url');
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return 1;
    }
}
