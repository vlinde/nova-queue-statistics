<?php

namespace Vlinde\NovaQueueStatistics\Notification;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\FailedJobMonitor\Notification as BaseNotification;
use Vlinde\NovaQueueStatistics\Models\QueueStatistic;

class CustomNotificationStatistic extends BaseNotification
{
    protected $statistic;

    public function via($notifiable): array
    {
        return config('failed-job-monitor.channels');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('A job failed at ' . config('app.url'))
            ->line("Queue statistic set to status 'failed'");
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->from(config('env.APP_NAME'))
            ->to(config('failed-job-monitor.slack.channel'))
            ->content(' ' . config('app.url'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields([
                    'Description' => "Queue statistic set to status - 'failed'",
                    '#ID' => $this->statistic->id,
                    'Connection' => $this->statistic->connection,
                    'Queue' => $this->statistic->queue,
                    'Server localtime' => Carbon::now()->toDateTimeLocalString()
                ]);
            });
    }

    public function setStatistic(QueueStatistic $statistic): self
    {
        $this->statistic = $statistic;

        return $this;
    }
}
