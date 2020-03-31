<?php

namespace Vlinde\NovaQueueStatistics\Notification;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Str;
use Spatie\FailedJobMonitor\Notification as BaseNotification;
use Vlinde\NovaQueueStatistics\Classes\ThrottledNotification;

class CustomNotification extends BaseNotification implements ThrottledNotification
{
    /** @var \Illuminate\Queue\Events\JobFailed */
    protected $event;

    public function via($notifiable): array
    {
        return config('failed-job-monitor.channels');
    }

    public function getEvent(): JobFailed
    {
        return $this->event;
    }

    public function setEvent(JobFailed $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('A job failed at ' . config('app.url'))
            ->line("Exception message: {$this->event->exception->getMessage()}")
            ->line("Job class: {$this->event->job->resolveName()}")
            ->line("Job body: {$this->event->job->getRawBody()}")
            ->line("Exception: {$this->event->exception->getTraceAsString()}");
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->from(config('env.APP_NAME'))
            ->to(config('failed-job-monitor.slack.channel'))
            ->content('A job failed at ' . config('app.url'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields([
                    'Exception message' => $this->event->exception->getMessage(),
                    'Job class' => $this->event->job->resolveName(),
                ]);
            });
    }

    public function throttleDecayMinutes(): int
    {
        return config('queue_statistics.throttle_decay');
    }

    public function throttleKeyId()
    {
        if ($this->getEvent()->exception instanceof \Exception) {
            return Str::kebab($this->getEvent()->exception->getMessage());
        }

        // fall back throttle key, use the notification name...
        return static::class;
    }
}
