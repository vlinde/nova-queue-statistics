<?php

return [

    /*
     * The notification that will be sent when a job fails.
     */
    'notification' => \Vlinde\NovaQueueStatistics\Notification\CustomNotification::class,

    /*
     * The notifiable to which the notification will be sent. The default
     * notifiable will use the mail and slack configuration specified
     * in this config file.
     */
    'notifiable' => \Vlinde\NovaQueueStatistics\Notifiable\CustomNotifiable::class,

    /*
     * By default notifications are sent for all failures. You can pass a callable to filter
     * out certain notifications. The given callable will receive the notification. If the callable
     * return false, the notification will not be sent.
     */
    'notificationFilter' => null,

    /*
     * The channels to which the notification will be sent.
     */
    'channels' => ['slack'],
//    'channels' => ['mail', 'slack'],

    'mail' => [
        'to' => 'miret.bogdan@vlinde.com',
    ],

    'slack' => [
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
        'channel' => env('SLACK_FAILED_JOB_CHANNEL'),
    ],

    'statistic' => [
        'notification' => \Vlinde\NovaQueueStatistics\Notification\CustomNotificationStatistic::class
    ]
];
