<?php

namespace Vlinde\NovaQueueStatistics;

use Illuminate\Notifications\Notification;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Vlinde\NovaQueueStatistics\Http\Middleware\Authorize;
use Vlinde\NovaQueueStatistics\Models\QueueStatistic;
use Vlinde\NovaQueueStatistics\Observers\QueueStatisticObserver;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nova-queue-statistics');

        $this->publishes([
            __DIR__ . '/../src/Migrations/create_queue_statistics_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_queue_statistics_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__
            . '/../src/config/queue_statistics.php' => config_path('queue_statistics.php'),
        ], 'config');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });

        QueueStatistic::observe(QueueStatisticObserver::class);

        app(QueueManager::class)->failing(function (JobFailed $event) {
            $notifiable = app(config('queue_statistics.notifiable'));

            $notification = app(config('queue_statistics.notification'))->setEvent($event);

            if (!$this->isValidNotificationClass($notification)) {
                throw new \Exception("Class " . get_class($notification) . " is an invalid notification class. " .
                    'A notification class must extend ' . Notification::class);
            }

            if ($this->shouldSendNotification($notification)) {
                $notifiable->notify($notification);
            }
        });
    }

    public function isValidNotificationClass($notification): bool
    {
        if (get_class($notification) === Notification::class) {
            return true;
        }

        if (is_subclass_of($notification, Notification::class)) {
            return true;
        }

        return false;
    }

    public function shouldSendNotification($notification)
    {
        $callable = config('queue_statistics.notificationFilter');

        if (!is_callable($callable)) {
            return true;
        }

        return $callable($notification);
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/nova-queue-statistics')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
